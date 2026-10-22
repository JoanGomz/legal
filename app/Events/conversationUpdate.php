<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class conversationUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->sendToAbly();
    }

    private function sendToAbly()
    {
        try {
            $data = [
                'name' => 'ConversationUpdate',
                'data' => [
                    'event' => 'conversation_updated',
                    'timestamp' => now()->toISOString(),
                    'message' => 'Conversación actualizada'
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
                    //Linea original llamando al .env por el token
                    //'Authorization: Basic ' . base64_encode(env('ABLY_KEY')),
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
                Log::info("Ably OK: mensaje publicado correctamente en security-chat");
            }
        } catch (\Exception $e) {
            error_log('Error en conversationUpdate: ' . $e->getMessage());
        }
    }
}
