<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Admin\RoleServiceInterface;
use App\Contracts\Admin\PermissionServiceInterface;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected $roleService;
    protected $permissionService;

    public function __construct(RoleServiceInterface $roleService, PermissionServiceInterface $permissionService)
    {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $roles = $this->roleService->getAllActiveRoles();
        $permissions = $this->permissionService->getAllActivePermissions();

        $response = [
            'roles' => $roles->toArray(),
            'permissions' => $permissions->toArray()
        ];

        return $this->responseLivewire('success', '', $response);
    }

    public function create()
    {
        $permissions = $this->permissionService->getAllActivePermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $role = $this->roleService->createRole($request);
        if (is_array($role)) {
            return $this->responseLivewire('error', $role['message'], []);
        }

        return $this->responseLivewire('success', 'Rol creado exitosamente', $role);
    }

    public function show(Role $role)
    {
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = $this->permissionService->getAllActivePermissions();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, int $roleId)
    {
        $role = $this->getRole($roleId);
        if (is_array($role)) {
            return $role;
        }

        $role = $this->roleService->updateRole($request, $role);
        if (is_array($role)) {
            return $this->responseLivewire('error', $role['message'], []);
        }
        return $this->responseLivewire('success', 'Rol actualizado exitosamente', $role);
    }

    public function destroy(int $roleId)
    {
        $role = $this->getRole($roleId);
        if (is_array($role)) {
            return $role;
        }

        $role = $this->roleService->deleteRole($role);
        return $this->responseLivewire('success', 'Rol eliminado exitosamente', []);
    }

    private function getRole(int $roleId)
    {
        $role = $this->roleService->findById($roleId);
        if (!$role) {
            return $this->responseLivewire('error', 'El rol no existe', []);
        }

        return $role;
    }
}
