<?php

namespace App\Services\Operation;

use App\Http\Controllers\Operation\ParksController;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ParksService extends BaseService
{
    public function __construct(ParksController $mall)
    {
        parent::__construct($mall);
    }

    public function getAllMalls()
    {
        return DB::table('centroComercial', 'cc')
            ->join('ciudad AS c', 'c.id', '=', 'cc.id_ciudad')
            ->leftJoin('compania AS co', 'co.id', '=', 'cc.compania_id')
            ->leftJoin('users AS uc', 'uc.id', '=', 'cc.user_creator')
            ->leftJoin('users AS uu', 'uu.id', '=', 'cc.user_last_update')
            ->select('cc.id', 'cc.nombre', 'cc.horarios', 'cc.direccion', 'c.nombre AS nombre_ciudad', 'c.id AS id_ciudad', 'cc.create_at', 'cc.update_at', 'cc.delete_at', 'uc.name AS user_creator', 'uu.name AS user_last_update', 'co.nombre AS company_name', 'co.nit AS nit')
            ->where('cc.is_deleted', '=', 0)
            ->get();
    }
}
