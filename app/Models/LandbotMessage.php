<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandbotMessage extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'id',
        'uuid',
        'author_type',
        'conversation_data',
        'raw_data',
        'customer_phone',
        'landbot_chat_id',
        'conversation_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_creator',
        'user_last_update',
        'user_delete'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'conversation_data' => 'array',
        'message_timestamp' => 'datetime',
        'conversation_date' => 'datetime',
        'is_finished' => 'boolean'
    ];
}
