<?php

namespace App\Contracts\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

interface RoleServiceInterface
{
    public function getAllActiveRoles();
    public function createRole(Request $request);
    public function updateRole(Request $request, Role $role);
    public function deleteRole(Role $role);
    public function findById(int $roleId);
}
