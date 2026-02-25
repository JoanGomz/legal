<?php

namespace App\Services\Admin;

use App\Contracts\Admin\PermissionServiceInterface;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionServiceInterface
{
    public function getAllActivePermissions()
    {
        return Permission::all();
    }
    public function getPaginated($page, $items, $search){
        $query = Permission::query();


        // buscador - AGRUPA TODAS LAS CONDICIONES DE BÚSQUEDA
        if (!empty($search)) {
            $query->where(function ($mainQuery) use ($search) {
                // Búsqueda en campos directos del usuario
                $mainQuery->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('id', 'desc');
        return $query->paginate($items, ['*'], 'page', $page);
    }
    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
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
            'name' => 'required|string'
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
        return Permission::where('id', $id)->first();
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
                return $searcPermission;
            }

            return [
                'status' => 'error',
                'message' => 'Rol Existente'
            ];
        }
    }
}
