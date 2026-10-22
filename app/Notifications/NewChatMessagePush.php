<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewChatMessagePush extends Notification
{
    public function __construct(
        protected $message,
        protected $conversation
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $bodyText = $this->message->conversation_data['body'] ?? 'Nuevo mensaje recibido';

        return (new WebPushMessage)
            ->title('Nuevo mensaje - ' . ($this->conversation->name ?? 'Cliente'))
            ->body(is_string($bodyText) ? $bodyText : 'Nuevo mensaje recibido')
            ->action('Ver conversación', 'open_chat')
            ->data(['url' => '/chat/' . $this->conversation->id]) // ajusta a tu ruta real
            ->options(['TTL' => 1000]);
    }
}
