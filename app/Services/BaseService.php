<?php

namespace App\Services;

use App\Contracts\Base\BaseServiceInterface;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BaseService implements BaseServiceInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model::where('is_deleted', 0)->get();
    }

    public function getPaginated($page, $items, $search = '')
    {
        $query = $this->model->query();
        $query->where('is_deleted', 0);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
            $query->orWhere('id', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($items, ['*'], 'page', $page);
    }

    public function findById(int $id)
    {
        return $this->model->where('id', $id)->where('is_deleted', 0)->first();
    }

    public function create(array $request)
    {
        return $this->model->create($request);
    }

    public function update(array $request, int $id)
    {
        try {
            $record = $this->findById($id);
            $record->update($request);
            return $record;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(int $id)
    {
        $record = $this->model->where('id', $id)->where('is_deleted', 0);
        $record->update(['is_deleted' => 1, 'deleted_at' => now(), 'user_last_update' => Auth::id()]);
        return $record;
    }
}
