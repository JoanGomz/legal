<?php

namespace App\Http\Controllers;

use App\Services\LandbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LandbotWebhookController extends Controller
{
    private LandbotService $landbotService;

    public function __construct(LandbotService $landbotService)
    {
        $this->landbotService = $landbotService;
    }

    /**
     * Maneja las notificaciones entrantes de Landbot
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Webhook de Landbot recibido', ['data' => $request->all()]);

        try {
            $messageId = $this->landbotService->processWebhookData($request->all());
            return $this->responseLivewire('success', 'success', $messageId);
        } catch (\Exception $e) {
            Log::error('Error procesando webhook de Landbot', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $request->all()
            ]);

            return $this->responseLivewire('error', 'Error interno del servidor', $e->getMessage());
        }
    }

    /**
     * Obtener historial de conversación por conversation_id
     */
    public function getConversationHistory(int $conversation_id)
    {
        try {
            $response = $this->landbotService->getConversationHistory($conversation_id);
            return $this->responseLivewire('success', 'success', $response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las conversaciones
     */
    public function getAllConversations($status = null, $userAsingId = null, $search = '')
    {
        try {
            $conversations = $this->landbotService->getAllConversations($status, $userAsingId, $search);
            return $this->responseLivewire('success', 'success', $conversations);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar una nota en la conversación
     */
    public function saveNote(Request $request, int $conversationId)
    {
        try {
            $note = $request->input('note');
            $conversation = $this->landbotService->saveNote($conversationId, $note);
            return $this->responseLivewire('success', 'Nota guardada correctamente', $conversation);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar el estado de una conversación
     */
    public function changeStatusConversation(Request $request, int $conversationId)
    {
        try {
            $status = $request->input('status');
            $this->landbotService->changeConversationStatus($conversationId, $status);
            return $this->responseLivewire('success', 'Estado de la conversación actualizado correctamente', []);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reiniciar el bot para una conversación específica
     */
    public function restartBot(int $conversation_id)
    {
        try {
            $response = $this->landbotService->restartBot($conversation_id);
            return $this->responseLivewire('success', 'Bot reiniciado correctamente', $response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Asignar un usuario a una conversación
     */
    public function assignUserToConversation(Request $request, int $conversationId)
    {
        try {
            $userId = $request->input('user_id');
            $this->landbotService->assignUserToConversation($conversationId, $userId);
            return $this->responseLivewire('success', 'Usuario asignado a la conversación correctamente', []);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Desasignar usuarios
     */
    public function unassignUser()
    {
        try {
            $this->landbotService->unassignUsers();
            return $this->responseLivewire('success', 'Usuario desasignado de la conversación correctamente', []);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getConversationsWithSecurity(?string $status = null, ?int $userAsingId = null, ?string $search = '')
    {
        try {
            $conversations = $this->landbotService->getConversationsWithSecurity($status, $userAsingId, $search);
            return $this->responseLivewire('success', 'success', $conversations);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
