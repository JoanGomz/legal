<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Admin\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Operation\CentroComercial;
use App\Models\User;
use App\Services\Operation\CentroComercialService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index(): array
    {
        try {
            $users = $this->userService->getAllActiveUsers();
            return $this->responseLivewire('success', '', $users);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    /**
     * retorna todas los items paginadas
     */
    public function indexPaginated($page, $items, $search = '')
    {
        try {
            $response = $this->userService->getPaginated($page, $items, $search);

            return $this->responseLivewire('success', 'success', $response);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function store(Request $request): array
    {
        try {
            $this->validateEmail($request->email);

            if ($request->id_centro_comercial) {
                $mall = new CentroComercialService(new CentroComercial())->findById($request->id_centro_comercial);
                if (!$mall instanceof CentroComercial) {
                    return $this->responseLivewire('error', 'El centro comercial no existe');
                }
            }


            $user = $this->userService->createUser($request);
            $user->syncRoles($request->role_check);

            return $this->responseLivewire('success', 'Usuario creado correctamente', $user);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function update(Request $request, int $userId): array
    {
        try {
            $user = $this->userService->findById($userId);
            if (!$user) {
                return $this->responseLivewire('error', 'El usuario no existe', []);
            }

            if ($request->email != $user->email) {
                $this->validateEmail($request->email);
            }

            $user = $this->userService->updateUser($request, $user);
            $user->syncRoles($request->role_check);
            return $this->responseLivewire('success', 'Usuario Actualizado correctamente', $user);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    public function destroy(int $userId): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return $this->responseLivewire('error', 'El usuario no existe', []);
            }

            $this->userService->deleteUser($user);
            return $this->responseLivewire('success', 'El usuario se eliminó correctamente', []);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    private function validateEmail(string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            throw new \Exception('El email ya está en uso');
        }
    }
}
