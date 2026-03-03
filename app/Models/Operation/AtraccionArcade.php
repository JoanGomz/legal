<?php

namespace App\Models\Operation;

use App\Models\BaseModel;
use App\Models\User;

class AtraccionArcade extends BaseModel
{
    protected $table = 'atraccion_arcade';

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'tipo',
        'capacidad',
        'promedio_consumo',
        'tiempo_juego',
        'modo_pago',
        'numero_serie',
        'device_mac',
        'id_park',
        'ubicacion',
        'estado',
        'is_deleted',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_creator',
        'user_last_update',
    ];

    public function park()
    {
        return $this->belongsTo(Parks::class, 'id_park');
    }

    public function userCreator()
    {
        return $this->belongsTo(User::class, 'user_creator');
    }

    public function userLastUpdate()
    {
        return $this->belongsTo(User::class, 'user_last_update');
    }
}
