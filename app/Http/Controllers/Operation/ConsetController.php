<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\Consent\StoreConsentRequest;
use App\Services\Operation\ConsentService;
use Symfony\Component\HttpFoundation\Request;

class ConsetController extends Controller
{
    private ConsentService $ConsetService;

    public function __construct(ConsentService $ConsetService)
    {
        $this->ConsetService = $ConsetService;
    }

    /**
     * retorna todas las facturas paginadas
     */
    public function indexPaginated($page, $items, $search = '')
    {
        try {
            $response = $this->ConsetService->getPaginated($page, $items, $search);
            return $this->responseLivewire('success', 'success', $response);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request): array
    {
        try {
            $requestData = $request->validate((new StoreConsentRequest())->rules());
            $requestData['ip_address'] = $request->ip();
            $requestData['user_agent'] = $request->header('User-Agent');

            $conset = $this->ConsetService->saveConset($requestData);
            return $this->responseLivewire('success', 'El consentimiento se creó exitosamente', $conset);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
}
