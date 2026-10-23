<?php

namespace App\Services\Operation;

use App\Models\LandbotConversations;
use App\Models\Operation\Clients;
use App\Services\BaseService;

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
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $u = auth()->user()->park_id;

        if (auth()->user()->hasRole('Admin')) {
            $query->where('parks_id', $u);
        } else {
            $query->where('parks_id', $parkId);
            $query->where('brand_id', $brandId);
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
}
