<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookmarks')) {
            return;
        }

        Schema::table('bookmarks', function (Blueprint $table) {
            if (!Schema::hasColumn('bookmarks', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }
        });

        if ($this->isMysql() && Schema::hasColumn('bookmarks', 'session_id') && !$this->isNullable('bookmarks', 'session_id')) {
            Schema::table('bookmarks', function (Blueprint $table) {
                $table->string('session_id')->nullable()->change();
            });
        }

        Schema::table('bookmarks', function (Blueprint $table) {
            if (!$this->hasIndex('bookmarks', 'bookmarks_user_id_manga_slug_unique')) {
                $table->unique(['user_id', 'manga_slug']);
            }
        });
    }

    public function down(): void
    {
        // Backfill migration for existing installs. Fresh schema already owns this shape.
    }

    private function isMysql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    private function isNullable(string $table, string $column): bool
    {
        return (bool) DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
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
