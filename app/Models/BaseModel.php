<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $table = $model->getTable();
            $userId = Auth::id();

            $now = $model->freshTimestamp();
            if (Schema::hasColumn($table, 'created_at')) {
                $model->created_at = $now;
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $model->updated_at = $now;
            }

            if ($userId) {
                if (Schema::hasColumn($table, 'user_creator')) {
                    $model->user_creator = $userId;
                }
                if (Schema::hasColumn($table, 'user_last_update')) {
                    $model->user_last_update = $userId;
                }
            }
        });

        static::updating(function ($model) {
            $table = $model->getTable();

            if (Schema::hasColumn($table, 'updated_at')) {
                $model->updated_at = $model->freshTimestamp();
            }

            if (Auth::check() && Schema::hasColumn($table, 'user_last_update')) {
                $model->user_last_update = Auth::id();
            }
        });
    }
}
