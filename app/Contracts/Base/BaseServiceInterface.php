<?php

namespace App\Contracts\Base;

interface BaseServiceInterface
{
    public function getAll();
    public function findById(int $id);
    public function create(array $request);
    public function update(array $request, int $id);
    public function delete(int $id);
}
