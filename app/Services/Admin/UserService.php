<?php

namespace App\Services\Admin;

use App\Contracts\Admin\UserServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function getAllActiveUsers($brandId = 2, $parkId = 13, array $columns = ['*'])
    {
        $query = User::where('status', 1)
            ->with('roles:name')
            ->with('park');

        // si el usuario tiene el permiso viewAll se muestran todas las registros si no se filtra por el id del centro comercial
        if (auth()->user()->hasRole('Admin')) {
            $query->where('park_id', auth()->user()->park_id);
        } else {
            $query->where('park_id', $parkId);
            $query->where('brand_id', $brandId);
        }

        return $query->get();
    }


    public function createUser(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email', 'password' => 'required']);
        $request['password'] = Hash::make($request->password);

        return User::create($request->only(['name', 'email', 'password', 'park_id']));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate(['name' => 'required']);
        $user->update($request->all());
        return $user;
    }

    public function deleteUser(User $user)
    {
        $user->update(['status' => 0]);
    }

    public function findById(int $id)
    {
        return User::where('id', $id)->where('status', 1)->first();
    }

    /**
     * Retorna los items paginadas
     * @param int $page
     * @param int $items
     * @param string $search
     */
    public function getPaginated($page = 1, $items = 10, $search = '', $brandId = 1, $parkId = 12)
    {
        $query = User::query();

        $query->with('roles:name');

        if (auth()->user()->hasRole('Admin')) {
            $query->where('park_id', auth()->user()->park_id);
        } else {
            $query->where('park_id', $parkId);
            $query->where('brand_id', $brandId);
        }

        // buscador - AGRUPA TODAS LAS CONDICIONES DE BÚSQUEDA
        if (!empty($search)) {
            $query->where(function ($mainQuery) use ($search) {
                // Búsqueda en campos directos del usuario
                $mainQuery->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('roles', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('id', 'desc');
        return $query->paginate($items, ['*'], 'page', $page);
    }
}
