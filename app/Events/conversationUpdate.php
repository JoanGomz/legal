<?php

namespace App\Events;

use Ably\AblyRest;
use Illuminate\Support\Facades\Log;

class conversationUpdate
{
    public function __construct()
    {
        $this->sendToAbly();
    }

    private function sendToAbly(): void
    {
        try {
            $ably = new AblyRest(config('services.ably.key'));

            $ably->channels->get('legal')->publish('conversationUpdate', [
                'event'     => 'conversation_updated',
                'timestamp' => now()->toISOString(),
                'message'   => 'Conversación actualizada',
            ]);

            Log::info('Ably funciona: ConversationUpdate enviado a legal');
        } catch (\Throwable $e) {
            Log::error('Error enviando ConversationUpdate a Ably: ' . $e->getMessage());
        }
    }
}
