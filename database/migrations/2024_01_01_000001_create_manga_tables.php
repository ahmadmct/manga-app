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
            $table->string('session_id')->index();
            $table->string('manga_slug');
            $table->string('manga_title');
            $table->string('manga_thumb')->nullable();
            $table->string('manga_type')->default('Manga');
            $table->timestamps();

            $table->unique(['session_id', 'manga_slug']);
        });

        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('manga_slug');
            $table->string('chapter_slug');
            $table->string('chapter_title')->nullable();
            $table->integer('progress')->default(0); // percentage
            $table->timestamps();

            $table->index(['session_id', 'manga_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('reading_histories');
    }
};
