<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRepository extends BaseRepository
{
    protected $model;

    public function __construct(Product $model) {
        $this->model = $model;
    }

    public function findActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function findByCategory(string $categoryId): Collection
    {
        return $this->model->where('category_uuid', $categoryId)->get();
    }

    public function findActiveByCategory(string $categoryId): Collection
    {
        return $this->model->where('is_active', true)
            ->where('category_uuid', $categoryId)
            ->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Product
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