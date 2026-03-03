<?php

namespace App\Services\Operation;

use App\Models\Operation\AtraccionArcade;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class AtraccionArcadeService extends BaseService
{
    public function __construct(AtraccionArcade $model)
    {
        parent::__construct($model);
    }
}
