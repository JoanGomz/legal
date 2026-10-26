<?php

namespace App\Services\Operation;

use App\Models\LandbotConversations;
use App\Models\Operation\Clients;
use App\Models\Operation\Consents;
use App\Services\BaseService;
use Carbon\Carbon;

class ClientService extends BaseService
{

    public function __construct(Clients $model)
    {
        parent::__construct($model);
    }

    public function getAllPaginated(int $page, int $itms, string $search, int $brandId = 1, int $parkId = 12): array
    {
        $query = $this->model->query();
        $query->where('is_deleted', 0);
        $query->with('park');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $auth = auth()->user();

        if ($auth) {
            if (auth()->user()->hasRole('Admin')) {
                $query->where('parks_id', $auth->park_id);
            } else {
                $query->where('parks_id', $parkId);
                $query->where('brand_id', $brandId);
            }
        }

        $total = $query->count();
        $clients = $query->skip(($page - 1) * $itms)
            ->take($itms)
            ->get();

        return [
            'data' => $clients,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $itms,
        ];
    }

    public function addClientLandbot(array $data)
    {
        $cleanPhone = preg_replace('/\D+/', '', $data['phone']);
        $data['phone'] = strlen($cleanPhone) > 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $client = Clients::query()->create($data);

        $landbotClient = LandbotConversations::where('phone', $client->phone)->get()->first();
        if ($landbotClient) {
            $landbotClient->update([
                'client_id' => $client->id,
            ]);
        }
        return $client;
    }

    public function getConsentByClient(string $phone): array
    {
        $cleanedPhone = preg_replace('/^57/', '', $phone);

        $client = Clients::where('phone', $cleanedPhone)->first();

        if (!$client) {
            return [
                'success' => false,
                'total'   => 0,
                'mensaje' => "😕 No encontramos tu registro.\nVerifica tus datos e intenta de nuevo.",
                'pdfs'    => [],
            ];
        }

        $consents = Consents::class::where('phone', $cleanedPhone)->get();

        if ($consents->isEmpty()) {
            return [
                'success' => false,
                'total'   => 0,
                'mensaje' => "📭 Aún no tienes consentimientos firmados.",
                'pdfs'    => [],
            ];
        }

        $total = $consents->count();

        $lista = $consents->values()->map(function ($c, $i) {
            $fecha = $c->created_at
                ? Carbon::parse($c->created_at)->format('d/m/Y h:i a')
                : 'Sin fecha';
            return ($i + 1) . ". 📅 {$fecha}\n🔗 {$c->url_pdf}";
        })->implode("\n\n");

        $titulo = $total === 1
            ? "Tienes *1* consentimiento firmado:"
            : "Tienes *{$total}* consentimientos firmados:";

        $mensaje = "📄 *Tus consentimientos*\n\n{$titulo}\n\n{$lista}\n\n👆 Toca el enlace para ver o descargar tu PDF.";

        return [
            'success' => true,
            'total'   => $total,
            'mensaje' => $mensaje,
            'pdfs'    => $consents->pluck('url_pdf')->values(),
        ];
    }
}
