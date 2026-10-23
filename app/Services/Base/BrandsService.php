<?php

namespace App\Services\Base;

use App\Models\Base\Brands;
use App\Services\BaseService;

class BrandsService extends BaseService
{
    public function __construct(Brands $model)
    {
        parent::__construct($model);
    }

    public function getAllPaginated(int $page, int $items, string $search)
    {
        $query = $this->model->query();
        $query->where('is_deleted', 0);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($items, ['*'], 'page', $page);
    }
}
