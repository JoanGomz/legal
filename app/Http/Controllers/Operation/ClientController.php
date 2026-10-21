<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\LandbotConversations;
use Illuminate\Http\Request;
use App\Services\Operation\ClientService;

class ClientController extends Controller
{
    private ClientService $service;

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function indexPaginated(int $page, int $itms, string $search): array
    {
        try {
            $clients = $this->service->getAllPaginated($page, $itms, $search);
            return $this->responseLivewire('success', 'Clientes obtenidos correctamente', $clients);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request): array
    {
        try {
            $client = $this->service->create($request->all());
            return $this->responseLivewire('success', 'Cliente agregado correctamente', $client);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function update(Request $request, int $id): array
    {
        try {
            $client = $this->service->update($request->all(), $id);
            return $this->responseLivewire('success', 'Cliente actualizado correctamente', $client);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function delete(int $id): array
    {
        try {
            $this->service->delete($id);
            return $this->responseLivewire('success', 'Cliente eliminado correctamente', []);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function existClientByPhone(Request $request): array
    {
        try {
            $validatedData = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
            ]);

            $client = $this->service->getAll()->where('phone', $validatedData['phone'])->first();
            if ($client) {
                $mall_uuid = $client->park->uuid;
                $cleanedPhone = preg_replace('/^57/', '', $validatedData['phone']);
                $chanel_landbot = LandbotConversations::where('phone', 'like', '%' . $cleanedPhone)->first();
                if ($chanel_landbot) {
                    $channel = $chanel_landbot->bot_id;
                }
                $response = $this->responseLivewire(
                    'success',
                    'Cliente encontrado.',
                    [
                        'exists' => true,
                        'mall_uuid' => $mall_uuid,
                        'client_uuid' => $client->uuid,
                        'channel' => $channel ?? null
                    ]
                );
            } else {
                $response = $this->responseLivewire('success', 'Cliente no encontrado', ['exists' => false]);
            }

            return $response;
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function addClientLandbot(Request $request): array
    {
        try {
            $client = $this->service->addClientLandbot($request->all());
            return $this->responseLivewire('success', 'Cliente agregado correctamente', $client);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
}
