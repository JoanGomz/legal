<?php

namespace App\Models\Operation;

use App\Models\BaseModel;
use Illuminate\Support\Str;

class Consents extends BaseModel
{
    protected $table = 'consents';

    // protected static function booted()
    // {
    //     static::creating(function ($model) {
    //         $model->uuid = (string) Str::uuid();
    //     });
    // }

    protected $fillable = [
        'id',
        // 'uuid',
        'code',
        'url_pdf',
        'park_id',
        'arcade_id',
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
        'check_cuatro',
        'check_cinco',
        'check_seis',
        'is_delete',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public function park()
    {
        return $this->belongsTo(Parks::class, 'park_id');
    }

    public function acarde()
    {
        return $this->belongsTo(AtraccionArcade::class, 'arcade_id');
    }
}
