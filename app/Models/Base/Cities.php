<?php

namespace App\Models\Base;

use App\Models\BaseModel;

class Cities extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'department_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'is_deleted'
    ];

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
}
