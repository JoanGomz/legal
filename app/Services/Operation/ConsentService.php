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

    public function getPaginated($page = 1, $items = 10, $search = '', $brandId = 1, $parkId = 12)
    {
        $query = $this->model->query();

        if (auth()->user()->hasRole('Admin')) {
            $query->where('park_id', auth()->user()->park_id);
        } else {
            $query->where('brand_id', $brandId);
            $query->where('park_id', $parkId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('document_number', 'like', "%$search%")
                    ->orWhere('minor_document_number', 'like', "%$search%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($items, ['*'], 'page', $page);
    }

    public function saveConsent(array $data)
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

            $dataSave = $data;
            $firstConsent = null;
            $count = 1;

            if (isset($data['childrens'])) {
                foreach ($data['childrens'] as $child) {
                    $dataSave['minor_document_number'] = $child['minor_document_number'] ?? null;
                    $dataSave['minor_document_type'] = $child['minor_document_type'] ?? null;
                    $dataSave['minor_full_name'] = $child['minor_full_name'];
                    $dataSave['minor_birth_date'] = $child['minor_birth_date'];

                    $consent = new Consents();
                    if ($count == 1) {
                        $consent->fill($dataSave);
                        $consent->save();
                        $firstConsent = $consent;
                    } else {
                        $dataSave['consents_id'] = $firstConsent->id ?? null;
                        $consent->fill($dataSave);
                        $consent->save();
                    }
                    $count++;
                }
            } else {
                $consent = new Consents();
                $consent->fill($dataSave);
                $consent->save();
                $firstConsent = $consent;
            }

            $char = str_contains($park->name, 'Barranquilla') ? "JPL" : "STSP";
            $code = $char . $park->id . "-" . $firstConsent->id;

            $view = str_contains($park->name, 'Barranquilla') ? 'pdf.Consents.generic' : 'pdf.Consents.consent';

            $pdf = Pdf::loadView($view, [
                'registration' => $firstConsent,
                'arcade' => $arcade,
                'code' => $code,
                'data' => $data
            ])
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true
                ]);

            $fileName = 'consents/consentimiento_' . $firstConsent->uuid . '_' . $firstConsent->id . '.pdf';
            Storage::disk('s3')->put($fileName, $pdf->output());

            $s3Url = Storage::disk('s3')->url($fileName);

            $firstConsent->url_pdf = $s3Url;
            $firstConsent->code = $code;
            $firstConsent->created_at = date('Y-m-d G:i:s');
            $firstConsent->save();

            $consents = Consents::where('consents_id', $firstConsent->id)->get();
            foreach ($consents as $consent) {
                $consent->url_pdf = $s3Url;
                $consent->code = $code;
                $consent->created_at = date('Y-m-d G:i:s');
                $consent->save();
            }

            return $firstConsent;
        } catch (\Exception $ex) {
            throw new \Exception('Error al guardar el consentimiento: ' . $ex->getMessage());
        }
    }
}
