<?php

namespace App\Contracts\Admin;

use App\Models\User;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function getAllActiveUsers();
    public function createUser(Request $request);
    public function updateUser(Request $request, User $user);
    public function deleteUser(User $user);
    public function findById(int $userId);
    public function getPaginated($page, $items, $search = '');
}
