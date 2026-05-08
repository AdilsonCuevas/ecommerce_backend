<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepository $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getById(string $id): ?\App\Models\Category
    {
        return $this->repository->find($id);
    }

    public function create(array $data): \App\Models\Category
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): ?\App\Models\Category
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}