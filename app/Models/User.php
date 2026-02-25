<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        'name',
        'email',
        'password',
        'status',
        'id_centro_comercial',
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

    /**
     * Set Values
     */
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    public function userCreator()
    {
        return $this->hasOne(self::class, 'id', 'user_creator');
    }

    public function userUpdate()
    {
        return $this->hasOne(self::class, 'id', 'user_last_update');
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
