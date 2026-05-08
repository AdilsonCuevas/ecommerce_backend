<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        private ProductRepository $repository
    ) {}

    public function getCatalog(): Collection
    {
        return $this->repository->findActive();
    }

    public function getById(string $id): ?\App\Models\Product
    {
        return $this->repository->find($id);
    }

    public function getByCategory(string $categoryId): Collection
    {
        return $this->repository->findActiveByCategory($categoryId);
    }

    public function create(array $data): \App\Models\Product
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): ?\App\Models\Product
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateStock(string $id, int $quantity): void
    {
        $product = $this->repository->find($id);
        if ($product) {
            $product->decrement('stock', $quantity);
        }
    }
}