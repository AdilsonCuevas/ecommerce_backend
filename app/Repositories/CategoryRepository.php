<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository extends BaseRepository
{
    protected $model = Category::class;

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Category
    {
        $model = $this->find($id);
        if ($model) {
            $model->update($data);
        }
        return $model;
    }

    public function delete(string $id): bool
    {
        $model = $this->find($id);
        return $model ? $model->delete() : false;
    }
}