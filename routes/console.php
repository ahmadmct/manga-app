<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('manga:clear-cache', function () {
    cache()->flush();
    $this->info('Manga API cache cleared.');
})->purpose('Clear all cached manga API responses');
