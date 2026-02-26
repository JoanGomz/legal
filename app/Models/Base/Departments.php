<?php

namespace App\Models\Base;

use App\Models\BaseModel;

class Departments extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'country_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'is_deleted'
    ];

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }
}
