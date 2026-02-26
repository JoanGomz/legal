<?php

namespace App\Services\Admin;

use App\Contracts\Admin\RoleServiceInterface;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleService implements RoleServiceInterface
{
    public function getAllActiveRoles()
    {
        return Role::where('status', 1)->with('permissions')->get();
    }

    public function createRole(Request $request)
    {
        $searchRole = Role::where('name', $request->name)->first();
        if ($searchRole instanceof Role) {
            if (!$searchRole->status) {
                $searchRole->update(['status' => 1]);
                return $searchRole;
            }

            return [
                'status' => 'error',
                'message' => 'Rol Existente'
            ];
        }

        $this->validateData($request);
        $role = Role::create($request->only(['name', 'description']));

        if ($request->has('permissions')) {
            $permissions = $this->validatePermissions($request, $role);

            if (isset($permissions['message'])) {
                return $permissions;
            }
        }

        return $role;
    }

    public function updateRole(Request $request, Role $role)
    {
        $this->validateData($request);

        if ($request->has('permissions')) {
            $permissions = $this->validatePermissions($request, $role);
            if (isset($permissions['message'])) {
                return $permissions;
            }
        }
        $role->update($request->only(['name', 'description']));

        return $role;
    }

    public function deleteRole(Role $role)
    {
        $role->update(['status' => 0]);
    }

    public function findById(int $id)
    {
        return Role::where('id', $id)->where('status', 1)->first();
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);
    }

    private function validatePermissions(Request $request, Role | null $role)
    {
        $permissions = (new PermissionService)->validatePermissions($request->permissions);
        $validatedPermissions = collect($permissions)->pluck('id')->toArray();

        if (count($validatedPermissions) !== count($request->permissions)) {
            $permissionsNotFound = array_diff($request->permissions, $validatedPermissions);
            return [
                'message' => 'Permisos ' . implode(',', $permissionsNotFound) . ' no encontrados'
            ];
        }

        return $role ? $role->syncPermissions($validatedPermissions) : true;
    }
}
