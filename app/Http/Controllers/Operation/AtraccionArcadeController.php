<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Services\Operation\AtraccionArcadeService;
use Illuminate\Http\Request;

class AtraccionArcadeController extends Controller
{
    private AtraccionArcadeService $atraccionArcadeService;

    public function __construct(AtraccionArcadeService $atraccionArcadeService)
    {
        $this->atraccionArcadeService = $atraccionArcadeService;
    }

    public function index()
    {
        try {
            $atraccionArcade = $this->atraccionArcadeService->getAll();
            return $this->responseLivewire('success', '', $atraccionArcade);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function getPaginated($page, $items, $search = '')
    {
        try {
            $response = $this->atraccionArcadeService->getPaginated($page, $items, $search);
            return $this->responseLivewire('success', 'success', $response);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request)
    {
        try {
            $atraccionArcade = $this->atraccionArcadeService->create($request->all());
            return $this->responseLivewire('success', 'Atracción Arcade creada exitosamente', $atraccionArcade);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $atraccionArcade = $this->atraccionArcadeService->update($request->all(), $id);
            return $this->responseLivewire('success', 'Atracción Arcade actualizada exitosamente', $atraccionArcade);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        try {
            $this->atraccionArcadeService->delete($id);
            return $this->responseLivewire('success', 'Atracción Arcade eliminada exitosamente', []);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
}
