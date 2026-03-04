<?php

namespace App\Models\Operation;

use App\Models\BaseModel;

class Consents extends BaseModel
{
    protected $table = 'consents';
    protected $fillable = [
        'id',
        'code',
        'url_pdf',
        'park_id',
        'arcade',
        'document_number',
        'document_type',
        'full_name',
        'relationship',
        'phone',
        'email',
        'minor_document_number',
        'minor_document_type',
        'minor_full_name',
        'minor_birth_date',
        'check_uno',
        'check_dos',
        'check_tres',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public function park()
    {
        return $this->belongsTo(Parks::class, 'park_id');
    }
}
