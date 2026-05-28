<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('reading_histories')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            }
        });

        Schema::table('reading_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('reading_histories', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (!Schema::hasColumn('reading_histories', 'manga_title')) {
                $table->string('manga_title')->nullable()->after('manga_slug');
            }

            if (!Schema::hasColumn('reading_histories', 'manga_thumb')) {
                $table->string('manga_thumb')->nullable()->after('manga_title');
            }
        });

        if ($this->isMysql() && Schema::hasColumn('reading_histories', 'session_id') && !$this->isNullable('reading_histories', 'session_id')) {
            Schema::table('reading_histories', function (Blueprint $table) {
                $table->string('session_id')->nullable()->change();
            });
        }

        Schema::table('reading_histories', function (Blueprint $table) {
            if (!$this->hasIndex('reading_histories', 'reading_histories_user_id_manga_slug_index')) {
                $table->index(['user_id', 'manga_slug']);
            }

            if (!$this->hasIndex('reading_histories', 'reading_histories_user_id_chapter_slug_unique')) {
                $table->unique(['user_id', 'chapter_slug']);
            }
        });
    }

    public function down(): void
    {
        // This migration backfills older databases. Fresh installs already define
        // these columns in their create-table migrations, so rollback is a no-op.
    }

    private function isMysql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    private function isNullable(string $table, string $column): bool
    {
        $database = DB::getDatabaseName();

        return (bool) DB::table('information_schema.columns')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->where('is_nullable', 'YES')
            ->exists();
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (!$this->isMysql()) {
            return true;
        }

        return (bool) DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
