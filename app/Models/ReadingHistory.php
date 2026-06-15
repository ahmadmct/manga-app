<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'manga_slug',
        'manga_title',
        'manga_thumb',
        'chapter_slug',
        'chapter_title',
        'progress',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}