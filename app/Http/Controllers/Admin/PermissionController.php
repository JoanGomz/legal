<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Admin\PermissionServiceInterface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionServiceInterface $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $permissions = $this->permissionService->getAllActivePermissions();
        return $permissions;
    }
    public function indexPaginated($page, $items, $search = '')
    {
        try {
            $response = $this->permissionService->getPaginated($page, $items, $search);
            return $this->responseLivewire('success', 'Datos obtenidos correctamente', $response);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }
    public function store(Request $request)
    {
        if ($request->name) {
            $permission = Permission::where('name', $request->name)->first();
            if ($permission) {
                return $this->responseLivewire('error', 'El permiso ya existe', []);
            }
        }

        $permission = $this->permissionService->createPermission($request);
        return $this->responseLivewire('success', 'Permiso creado correctamente', $permission->toArray());
    }

    public function update(Request $request, int $permissionId)
    {
        $permission = $this->getPermission($permissionId);

        $permission = $this->permissionService->updatePermission($request, $permission);
        if (is_array($permission)) {
            return $this->responseLivewire('error', $permission['message'], []);
        }

        return $this->responseLivewire('success', 'Permiso Actualizado correctamente', $permission->toArray());
    }

    public function destroy(int $permissionId)
    {
        $permission = $this->getPermission($permissionId);
        if (is_array($permission)) {
            return $permission;
        }

        $permission = $this->permissionService->deletePermission($permission);
        return $this->responseLivewire('success', 'Permiso Eliminado correctamente', $permission);
    }

    private function getPermission(int $permissionId)
    {
        $permission = $this->permissionService->getPermissionById($permissionId);
        if (!$permission) {
            return $this->responseLivewire('error', 'El permiso no existe', []);
        }

        return $permission;
    }
}
