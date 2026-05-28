<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'session_id',
    'manga_slug',
    'manga_title',
    'manga_thumb',
    'chapter_slug',
    'chapter_title',
    'progress',
])]
class ReadingHistory extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
