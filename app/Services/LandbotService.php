<?php

namespace App\Services;

use App\Events\conversationUpdate;
use App\Events\MessageSent;
use App\Models\LandbotConversations;
use App\Models\LandbotMessage;
use App\Models\Operation\Clients;
use App\Models\Operation\Parks;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\NewChatMessagePush;

class LandbotService
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => "https://api.landbot.io/",
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'token ' . "83e83f7dc4bf589fbb5180cfefeabf069e51606e"
            ],
            'timeout' => 50,
            'http_errors' => false,
            'verify' => storage_path('certificates/cacert.pem')
        ]);
    }

    /**
     * Procesa los datos del webhook de Landbot
     */
    public function processWebhookData(array $data): string
    {
        if (empty($data)) {
            throw new \Exception('No se recibieron datos');
        }

        $messageData = $data['messages'][0];
        $phoneWithoutCountryCode = substr($messageData['customer']['phone'], 2);
        $messageData['customer']['phone'] = $phoneWithoutCountryCode;

        DB::beginTransaction();
        try {
            $conversation = $this->saveConversation($messageData);
            $messageId = $this->saveMessage($messageData, $conversation);
            DB::commit();

            return $messageId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**Guarda un archivo en S3
     * 
     */
    public function saveFileS3(array $data)
    {

        $fileData = $data['data']['body'];
        $chatId = $data['_raw']['chat'];

        // Verificar si es un objeto TemporaryUploadedFile
        if ($fileData instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $filePath = $fileData->getRealPath();
            $originalName = $fileData->getClientOriginalName();
        } else {
            // Si es un array (fallback para compatibilidad)
            $filePath = $fileData['realPath'] ?? $fileData['path'];
            $originalName = $fileData['originalName'] ?? $fileData['name'];
        }

        // Validar que el archivo existe
        if (!file_exists($filePath)) {
            throw new \Exception("El archivo temporal no se encontró: {$filePath}");
        }

        // Validar que el archivo es legible
        if (!is_readable($filePath)) {
            throw new \Exception("No se puede leer el archivo: {$filePath}");
        }

        // Decodificar nombre si está en base64
        $originalName = $this->decodeFileName($originalName);

        // Obtener la extensión del archivo original
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = pathinfo($originalName, PATHINFO_FILENAME);

        $s3Path = "files/chat/{$chatId}/" . time() . "_{$fileName}.{$extension}";

        try {
            // Leer el contenido del archivo 
            $fileContent = file_get_contents($filePath);

            if ($fileContent === false) {
                throw new \Exception("Error al leer el contenido del archivo");
            }

            // Subir a S3
            $success = Storage::disk('s3')->put($s3Path, $fileContent);

            if (!$success) {
                throw new \Exception("Error al subir el archivo a S3");
            }

            return Storage::disk('s3')->url($s3Path);
        } catch (\Exception $e) {
            \Log::error("Error en saveFileS3: " . $e->getMessage(), [
                'filePath' => $filePath,
                'originalName' => $originalName,
                'chatId' => $chatId,
                'fileDataType' => get_class($fileData)
            ]);

            throw $e;
        }
    }

    /**
     * Decodifica el nombre del archivo si está codificado en base64
     */
    private function decodeFileName($originalName)
    {
        if (strpos($originalName, '-meta') !== false) {
            $parts = explode('-meta', $originalName);
            if (isset($parts[1])) {
                // Extraer la extensión del final
                preg_match('/^(.+)-\.([^.]+)$/', $parts[1], $matches);

                if ($matches && count($matches) === 3) {
                    $encodedName = $matches[1];
                    $extension = $matches[2];

                    $decodedName = base64_decode($encodedName);
                    if ($decodedName) {
                        return $decodedName . '.' . $extension;
                    }
                }
            }
        }

        return $originalName;
    }

    /**
     * Guarda o encuentra una conversación
     */
    public function saveConversation(array $data): LandbotConversations
    {
        $conversation = LandbotConversations::where('landbot_chat_id', $data['_raw']['chat'])->first();

        if (!$conversation) {
            $client = Clients::where('phone', $data['customer']['phone'])
                ->where('is_deleted', 1)
                ->first();

            $conversation = LandbotConversations::create([
                'name' => $client ? $client->first_name : $data['customer']['name'],
                'phone' => $data['customer']['phone'],
                'landbot_chat_id' => $data['_raw']['chat'],
                'landbot_customer_id' => $data['customer']['id'],
                'status' => 'pendiente',
                'bot_id' => $data['channel']['id'] ?? null
            ]);

            if (!$conversation) {
                Log::error('Error al guardar la conversación', ['data' => $data]);
                throw new \Exception('No se pudo guardar la conversación');
            }
        }

        return $conversation;
    }

    /**
     * Guarda un mensaje
     */
    public function saveMessage(array $data, LandbotConversations $conversation): string
    {
        $chatId = $data['_raw']['chat'];
        $messageId = $chatId . '_' . time() . '_' . uniqid();

        $this->createNewMessage($data, $conversation, $messageId);

        // Enviar mensaje si es del usuario del sistema
        if ($data['_raw']['author_type'] == 'usuario') {
            $this->sendTextMessage($data, $conversation);
        }

        return $messageId;
    }


    /**
     * Crea un nuevo mensaje
     */
    private function createNewMessage(array $data, LandbotConversations $conversation, string $messageId): LandbotMessage
    {
        $messageData = [
            'raw_data' => json_encode($data),
            'customer_phone' => $data['customer']['phone'],
            'landbot_chat_id' => $data['_raw']['chat'],
            'author_type' => $data['_raw']['author_type'] == 'user' ? 'client' : ($data['_raw']['author_type'] == 'usuario' ? 'user' : 'bot'),
            'conversation_data' =>  $data['data'],
            'conversation_id' => $conversation->id,
        ];

        $newMessage = LandbotMessage::create($messageData);
        broadcast(new MessageSent($newMessage));

        Log::info('Nuevo mensaje guardado', [
            'id' => $newMessage->id,
            'landbot_chat_id' => $data['_raw']['chat'],
        ]);

        $conversation->updated_at = Carbon::now();
        if ($data['_raw']['author_type'] == 'user') {
            $conversation->status = 'pendiente';
        }

        if (!$conversation->user_asing_id && $data['_raw']['author_type'] == 'user') {
            $advisors = User::role('Asesor web')->get();

            foreach ($advisors as $advisor) {
                $advisor->notify(new NewChatMessagePush($newMessage, $conversation));
            }
        }
        $conversation->save();

        return $newMessage;
    }

    /**
     * Envía mensaje de texto a través de Landbot
     */
    public function sendTextMessage(array $data, LandbotConversations $conversation): void
    {
        $payload = [
            "message" => $data['data']['body'] ?? $data['data']['url']
        ];

        $response = $this->httpClient->post("v1/customers/{$conversation->landbot_customer_id}/send_text/", [
            'json' => $payload
        ]);

        if ($response->getStatusCode() == 412) {
            Log::error('Error al enviar mensaje a Landbot', [
                'status_code' => $response->getStatusCode(),
                'body' => (string)$response->getBody()
            ]);
            throw new \Exception('No se pueden enviar mensajes por WhatsApp 24h después del último mensaje del cliente');
        }

        if ($response->getStatusCode() !== 200) {
            Log::error('Error al enviar mensaje a Landbot', [
                'status_code' => $response->getStatusCode(),
                'body' => (string)$response->getBody()
            ]);
            throw new \Exception('Error al enviar mensaje: ' . (string)$response->getBody());
        }
    }

    /**
     * Obtiene el historial de conversación
     */
    public function getConversationHistory(int $conversationId): array
    {
        $messages = LandbotMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'conversation_id' => $conversationId,
            'messages' => $messages,
            'total_messages' => $messages->count()
        ];
    }

    /**
     * Obtiene todas las conversaciones con filtros
     */
    public function getAllConversations(?string $status = null, ?int $userAsingId = null, ?string $search = null)
    {
        $query = LandbotConversations::with([
            'lastMessage:id,conversation_id,conversation_data',
            'client:id,first_name,phone'
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($userAsingId) {
            $query->where('user_asing_id', $userAsingId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $query->where('bot_id', auth()->user()->park->bot_id);

        return $query->orderBy('updated_at', 'desc')->get();
    }

    /**
     * Guarda una nota en la conversación
     */
    public function saveNote(int $conversationId, string $note): LandbotConversations
    {
        if (empty($note)) {
            throw new \Exception('Nota no puede estar vacía');
        }

        $conversation = LandbotConversations::findOrFail($conversationId);
        $conversation->note = $note;
        $conversation->save();

        return $conversation;
    }

    /**
     * Cambia el estado de una conversación
     */
    public function changeConversationStatus(int $conversationId, string $status): void
    {
        if (empty($status)) {
            throw new \Exception('Estado no puede estar vacío');
        }

        $conversation = LandbotConversations::findOrFail($conversationId);
        $conversation->status = $status;

        if ($status == 'finalizado') {
            $conversation->is_assigned = 0;
            $conversation->user_asing_id = null;
        }

        $conversation->save();
    }

    /**
     * Reinicia el bot para una conversación
     */
    public function restartBot(int $conversationId): string
    {
        $conversation = LandbotConversations::findOrFail($conversationId);
        $mall = Parks::find(auth()->user()->mall_id);
        $botId = $mall->external_bot_id;

        $response = $this->httpClient->put("v1/customers/{$conversation->landbot_customer_id}/assign_bot/{$botId}/", [
            'json' => ["launch" => true]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Error al reiniciar el bot: ' . (string)$response->getBody());
        }

        return (string)$response->getBody();
    }

    /**
     * Asigna un usuario a una conversación
     */
    public function assignUserToConversation(int $conversationId, int $userId): void
    {
        if (empty($userId)) {
            throw new \Exception('ID de usuario no puede estar vacío');
        }

        $conversation = LandbotConversations::findOrFail($conversationId);
        $conversation->user_asing_id = $userId;
        $conversation->is_assigned = 1;
        $conversation->save();

        event(new conversationUpdate());
    }

    /**
     * Desasigna usuarios de conversaciones activas
     */
    public function unassignUsers(): void
    {
        $conversations = LandbotConversations::where('is_assigned', 1)
            ->where('status', '!=', 'finalizada')
            ->get();

        foreach ($conversations as $conversation) {
            $conversation->user_asing_id = null;
            $conversation->is_assigned = 0;
            $conversation->save();
        }
    }

    public function getConversationsWithSecurity(?string $status = null, ?int $userAsingId = null, ?string $search = null)
    {
        $query = LandbotConversations::with([
            'lastMessage:id,conversation_id,conversation_data',
            'client:id,first_name,phone'
        ])
            ->whereHas('messages', function ($q) {
                $q->whereRaw('LOWER(conversation_data) LIKE ?', ['%botón de pánico%']);
            });

        if ($status) {
            $query->where('status', $status);
        }

        if ($userAsingId) {
            $query->where('user_asing_id', $userAsingId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $query->where('bot_id', auth()->user()->mall->bot_id);

        return $query->orderBy('updated_at', 'desc')->get();
    }
}
