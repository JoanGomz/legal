<?php

namespace App\Services\Admin;

use App\Contracts\Admin\PermissionServiceInterface;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionServiceInterface
{
    public function getAllActivePermissions()
    {
        return Permission::where('status', 1)->get();
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string'
        ]);

        $validateExistName = $this->validateExistName($request);
        if (is_array($validateExistName)) {
            return $validateExistName;
        }

        return Permission::create($request->only(['name', 'description']));
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string'
        ]);

        if ($permission->name !== $request->name) {
            $validateExistName = $this->validateExistName($request);
            if (is_array($validateExistName)) {
                return $validateExistName;
            }
        }

        $permission->update($request->only(['name', 'description']));

        return $permission;
    }

    public function deletePermission(Permission $permission)
    {
        $permission->status = 0;
        $permission->save();
    }

    public function getPermissionById($id)
    {
        return Permission::where('status', 1)->where('id', $id)->first();
    }

    public function validatePermissions(array $ids)
    {
        return Permission::whereIn('id', $ids)->get();
    }

    private function validateExistName(Request $request)
    {
        $searcPermission = Permission::where('name', $request->name)->first();
        if ($searcPermission instanceof Permission) {
            if (!$searcPermission->status) {
                $searcPermission->update(['status' => 1]);
                return $searcPermission;
            }

            return [
                'status' => 'error',
                'message' => 'Rol Existente'
            ];
        }
    }
}
