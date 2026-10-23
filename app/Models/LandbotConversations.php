<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Operation\Clients;

class LandbotConversations extends BaseModel
{
    protected $table = 'landbot_conversations';

    protected $fillable = [
        'id',
        'uuid',
        'name',
        'phone',
        'client_id',
        'landbot_chat_id',
        'landbot_customer_id',
        'status',
        'note',
        'is_assigned',
        'user_assign_id',
        'bot_id',
        'brand_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_creator',
        'user_last_update',
        'user_delete'
    ];

    public function lastMessage()
    {
        return $this->hasOne(LandbotMessage::class, 'conversation_id')
            ->latest('created_at');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(LandbotMessage::class, 'conversation_id');
    }
}
