<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\Parks\StoreParksRequest;
use App\Http\Requests\Operation\Parks\UpdateParksRequest;
use App\Models\Operation\Parks;
use Illuminate\Http\Request;

class ParksController extends Controller
{

    public function index()
    {
        try {
            $query = Parks::query()->where('is_deleted', 0);
            if (auth()->user()->hasRole('Admin')) {
                $query->where('id', auth()->user()->park_id);
            }
            $parks = $query->get();
            return $this->responseLivewire('success', '', $parks);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request)
    {
        try {
            $requestData = $request->validate((new StoreParksRequest())->rules());
            $park = Parks::create($requestData);
            return $this->responseLivewire('success', 'El centro comercial se creó exitosamente', $park);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function update(Request $request, int $parkId)
    {
        try {
            $requestData = $request->validate((new UpdateParksRequest())->rules());
            $parks = Parks::find($parkId);
            if (!$parks instanceof Parks) {
                throw new \Exception('El centro comercial no existe');
            }
            $park = $parks->update($requestData);
            return $this->responseLivewire('success', 'El centro comercial se actualizó exitosamente', $park);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function destroy(int $parkId): array
    {
        try {
            $park = Parks::find($parkId);
            if (!$park) {
                return $this->responseLivewire('error', 'El parque no existe', []);
            }
            $park->update(['is_deleted' => 1]);
            return $this->responseLivewire('success', 'El parque se eliminó correctamente', []);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
}
