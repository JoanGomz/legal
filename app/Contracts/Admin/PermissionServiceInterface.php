<?php

namespace App\Contracts\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

interface PermissionServiceInterface
{
    public function getAllActivePermissions();
    public function createPermission(Request $request);
    public function updatePermission(Request $request, Permission $permission);
    public function deletePermission(Permission $permission);
    public function getPermissionById(int $permissionId);
}
