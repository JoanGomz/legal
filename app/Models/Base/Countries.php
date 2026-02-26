<?php

namespace App\Models\Base;

use App\Models\BaseModel;

class Countries extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'status',
        'created_at',
        'updated_at',
        'is_deleted'
    ];
}
