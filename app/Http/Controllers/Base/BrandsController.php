<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Services\Base\BrandsService;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    private BrandsService $service;

    public function __construct(BrandsService $service)
    {
        $this->service = $service;
    }

    public function indexPaginated(int $page, int $itms, string $search): array
    {
        try {
            $brands = $this->service->getAllPaginated($page, $itms, $search);
            return $this->responseLivewire('success', 'Marcas obtenidas correctamente', $brands);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request): array
    {
        try {
            $brand = $this->service->create($request->all());
            return $this->responseLivewire('success', 'Marca agregada correctamente', $brand);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function update(Request $request, int $id): array
    {
        try {
            $brand = $this->service->update($request->all(), $id);
            return $this->responseLivewire('success', 'Marca actualizada correctamente', $brand);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function delete(int $id): array
    {
        try {
            $this->service->delete($id);
            return $this->responseLivewire('success', 'Marca eliminada correctamente', []);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
}
