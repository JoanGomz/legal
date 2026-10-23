<?php

namespace App\Models\Operation;

use App\Models\Base\Cities;
use App\Models\BaseModel;

class Parks extends BaseModel
{
    protected $table = 'parks';
    protected $fillable = [
        'id',
        'uuid',
        'city_id',
        'name',
        'address',
        'type',
        'status',
        'brand_id',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id');
    }
}
