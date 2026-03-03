<?php

namespace App\Services\Operation;

use App\Models\Operation\Consents;
use App\Models\Operation\Parks;
use App\Services\BaseService;

class ConsentService extends BaseService
{

    public function __construct(Consents $model)
    {
        parent::__construct($model);
    }

    public function getPaginated($page, $items, $search = '')
    {
        $query = $this->model->query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($items, ['*'], 'page', $page);
    }

    public function saveConset($data)
    {
        try {
            $park = Parks::find($data['park_id']);
            if (!$park) {
                throw new \Exception('El parque no existe');
            }

            $conset = new Consents();
            $conset->fill($data);
            $conset->save();

            $code = "STSP" . $park->id . "-$conset->id";
            $conset->code = $code;
            $conset->save();
            return $conset;
        } catch (\Exception $ex) {
            throw new \Exception('Error al guardar el consentimiento: ' . $ex->getMessage());
        }
    }
}
