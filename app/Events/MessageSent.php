<?php

namespace App\Events;

use App\Models\LandbotMessage;
use Illuminate\Support\Facades\Log;

class MessageSent
{
    public $message;

    public function __construct(LandbotMessage $message)
    {
        $this->message = $message;
        $this->sendToAbly();
    }

    private function sendToAbly()
    {
        try {
            $data = [
                'name' => 'MessageSent',
                'data' => [
                    'message' => $this->message->toArray(),
                    'conversation_id' => $this->message->conversation_id,
                ]
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://rest.ably.io/channels/legal/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . base64_encode(config('services.ably.key')),
                    'Content-Type: application/json'
                ],
                CURLOPT_CAINFO => storage_path('certificates/cacert.pem'),
                CURLOPT_TIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 201) {
                Log::error("Error enviando a Ably: HTTP $httpCode - $response");
            } else {
                Log::info("Ably funciona: Mensaje en security chat");
            }
        } catch (\Exception $e) {
            error_log('Error en MessageSent: ' . $e->getMessage());
        }
    }
}
