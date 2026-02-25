<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Livewire\WithPagination;

trait traitCruds
{
    use WithPagination;

    // Variable para mantener la página actual
    public $page = 1;

    // Variables para filtros (opcional)
    public $search = '';
    public $perPage = 10;

    public $response;
    // Para mantener el estado de la paginación en la URL
    protected $queryString = ['page' => ['except' => 1]];

    public function resetPage()
    {
        // Debes usar la variable correcta según cómo esté configurada tu paginación
        $this->page = 1; // Esto podría ser $this->currentPage = 1, dependiendo de tu implementación
    }
    public function updatedSearch()
    {
        // Resetear la paginación cuando se actualiza la búsqueda
        $this->resetPage();
    }
    // Método para cambiar de página manualmente si necesitas botones personalizados
    public function goToPage($page)
    {
        $this->page = $page;
        if (method_exists($this, 'refreshData')) {
            $this->refreshData();
        }
    }
    protected function handleException(\Throwable $th, string $defaultMessage)
    {
        Log::error('Rol Management Error: ' . $th->getMessage());
        $this->callNotification(
            $th->getMessage(),
            'error'
        );
        $this->dispatch('hide-loading');
    }
    //METODO PARA LLAMAR A LA NOTIFICACIÓN
    public function callNotification($message, $type)
    {
        $message = $this->sanitizeMessage($message);
        $type = $this->sanitizeType($type);
        $messageJson = json_encode($message, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        $typeJson = json_encode($type, JSON_HEX_APOS | JSON_HEX_QUOT);

        $this->js("
        window.dispatchEvent(new CustomEvent('add-toast', {
            detail: {
                message: {$messageJson},
                type: {$typeJson},
                duration: 5000
            }
        }))
    ");
    }
    private function sanitizeMessage($message)
    {
        $message = (string) $message;
        $message = strip_tags($message);
        $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
        $message = preg_replace('/\s+/', ' ', $message);
        $message = mb_substr($message, 0, 500);
        return trim($message);
    }

    private function sanitizeType($type)
    {
        $validTypes = ['success', 'error', 'warning', 'info'];
        return in_array($type, $validTypes) ? $type : 'info';
    }
    public function showLoading($message = 'Cargando...')
    {
        $this->dispatch('show-loading', [
            'message' => $message
        ]);
    }
    protected function endPetition()
    {
        $this->callNotification($this->response['message'], $this->response['status']);
        $this->dispatch('hide-loading');
        if ($this->response['status'] == 'success') {
            if (method_exists($this, 'refreshData')) {
                $this->refreshData();
            }
            if (method_exists($this, 'clear')) {
                $this->clear();
            }
        }
    }
    public function validateWithSpinner()
    {
        try {
            $this->validate($this->rules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('hide-loading');
            $this->validate($this->rules());
        }
    }
    //OPCTIONAL EN CASO DE TENER REGLAS DIFERENTES (NO OLVIDAR CREAR LA FUNCIÓN rulesOnly EN EL COMPONENTE)
    public function validateWithSpinnerUpdate()
    {
        try {
            $this->validate($this->rulesOnly());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('hide-loading');
            $this->validate($this->rulesOnly());
        }
    }
    public function validarPermiso($permiso)
    {
        if (!auth()->user()->can($permiso)) {
            $this->callNotification('No tienes permisos para realizar esta acción', 'error');
            $this->dispatch('hide-loading');
            return false;
        }
        return true;
    }
}
