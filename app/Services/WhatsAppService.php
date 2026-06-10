<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function kirimWA(string $pesan): bool
    {
        try {

            $response = Http::withHeaders([
                'id'         => env('ZAWA_ID'),
                'session-id' => env('ZAWA_SESSION'),
                'Accept'     => '*/*',
            ])->post(env('ZAWA_API'), [
                'group' => env('ZAWA_PHONE'),
                'type'  => 'text',
                'text'  => $pesan,
            ]);
          

            Log::info('WA Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {

            Log::error('WA Error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
