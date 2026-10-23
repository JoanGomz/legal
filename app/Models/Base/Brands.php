<?php

namespace App\Models\Base;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brands extends BaseModel
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'id',
        'uuid',
        'name',
        'description',
        'is_deleted',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_creator',
        'user_last_update',
    ];
}
