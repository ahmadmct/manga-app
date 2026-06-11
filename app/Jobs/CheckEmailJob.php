<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Webklex\PHPIMAP\ClientManager;
use App\Services\WhatsAppService;

class CheckEmailJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $cm = new ClientManager();

        $client = $cm->make([
            'host'          => env('IMAP_HOST'),
            'port'          => env('IMAP_PORT'),
            'encryption'    => env('IMAP_ENCRYPTION'),
            'validate_cert' => false,
            'username'      => env('IMAP_USERNAME'),
            'password'      => env('IMAP_PASSWORD'),
            'protocol'      => 'imap',
        ]);

        $client->connect();

        $folder = $client->getFolder('INBOX');

        $messages = $folder
            ->messages()
            ->unseen()
            ->get();

        foreach ($messages as $message) {

            $subject = $message->getSubject();
            $from    = $message->getFrom()[0]->mail ?? '-';

            $pesan = "📧 EMAIL BARU\n\n";
            $pesan .= "From : {$from}\n";
            $pesan .= "Subject : {$subject}";
            $pesan .= "\n\n" . date('Y-m-d H:i:s');
            

            WhatsAppService::kirimWA($pesan);

            // tandai sudah dibaca
            $message->setFlag('Seen');
        }
    }
}