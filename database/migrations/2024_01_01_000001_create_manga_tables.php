<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('manga_slug');
            $table->string('manga_title');
            $table->string('manga_thumb')->nullable();
            $table->string('manga_type')->default('Manga');
            $table->timestamps();

            $table->unique(['user_id', 'manga_slug']);
            $table->unique(['session_id', 'manga_slug']);
        });

        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('manga_slug');
            $table->string('manga_title')->nullable();
            $table->string('manga_thumb')->nullable();
            $table->string('chapter_slug');
            $table->string('chapter_title')->nullable();
            $table->integer('progress')->default(0); // percentage
            $table->timestamps();

            $table->index(['user_id', 'manga_slug']);
            $table->index(['session_id', 'manga_slug']);
            $table->unique(['user_id', 'chapter_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('reading_histories');
    }
};
