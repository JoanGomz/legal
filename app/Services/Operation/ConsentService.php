<?php

namespace App\Services\Operation;

use App\Models\Operation\AtraccionArcade;
use App\Models\Operation\Consents;
use App\Models\Operation\Parks;
use App\Services\BaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Polyfill\Uuid\Uuid;

class ConsentService extends BaseService
{

    public function __construct(Consents $model)
    {
        parent::__construct($model);
    }

    public function getPaginated($page, $items, $search = '')
    {
        $query = $this->model->query();

        if (auth()->user()->hasRole('Admin')) {
            $query->where('park_id', auth()->user()->park_id);
        }

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

            $arcade = AtraccionArcade::find($data['arcade_id']);
            if (!$arcade) {
                throw new \Exception('La atracción arcade no existe');
            }

            $conset = new Consents();
            $conset->fill($data);
            $conset->save();

            $code = "STSP" . $park->id . "-" . $conset->id;

            $pdf = Pdf::loadView('pdf.consent', ['registration' => $conset, 'arcade' => $arcade, 'code' => $code])
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true
                ]);

            $fileName = 'consents/consentimiento_' . $conset->uuid . '_' . $conset->id . '.pdf';
            Storage::disk('s3')->put($fileName, $pdf->output());

            $s3Url = Storage::disk('s3')->url($fileName);

            $conset->url_pdf = $s3Url;
            $conset->code = $code;
            $conset->created_at = date('Y-m-d G:i:s');
            $conset->save();

            return $conset;
        } catch (\Exception $ex) {
            throw new \Exception('Error al guardar el consentimiento: ' . $ex->getMessage());
        }
    }
}
