<?php

namespace App\Events;

use Ably\AblyRest;
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

    private function sendToAbly(): void
    {
        try {
            $ably = new AblyRest(config('services.ably.key'));

            $ably->channels->get('legal')->publish('MessageSent', [
                'message'         => $this->message->toArray(),
                'conversation_id' => $this->message->conversation_id,
            ]);

            Log::info('Ably funciona: mensaje enviado a legal');
        } catch (\Throwable $e) {
            Log::error('Error enviando a Ably: ' . $e->getMessage(), [
                'code'  => $e->getCode(),
                'clase' => get_class($e),
            ]);
        }
    }
}
