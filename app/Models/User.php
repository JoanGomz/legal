<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Operation\Parks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'park_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'status',
        'user_creator',
        'user_last_update'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userCreator()
    {
        return $this->hasOne(self::class, 'id', 'user_creator');
    }

    public function userUpdate()
    {
        return $this->hasOne(self::class, 'id', 'user_last_update');
    }

    public function park()
    {
        return $this->belongsTo(Parks::class, 'park_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (true) {
                $model->user_creator = 1;
            }
        });

        static::updating(function ($model) {
            if (true) {
                $model->user_last_update = 1;
            }
        });
    }
}
