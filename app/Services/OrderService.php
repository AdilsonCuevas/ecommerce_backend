<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $repository
    ) {}

    public function getByUser(string $userUuid): Collection
    {
        return $this->repository->findByUser($userUuid);
    }

    public function getById(string $id): ?Order
    {
        return $this->repository->findWithItems($id);
    }

    public function create(string $userUuid, array $items, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($userUuid, $items, $notes) {
            $total = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = \App\Models\Product::where('uuid', $item['product_uuid'])->first();
                $quantity = $item['quantity'];
                $unitPrice = $product->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                $orderItems[] = [
                    'product_uuid' => $item['product_uuid'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock', $quantity);
            }

            $order = $this->repository->create([
                'user_uuid' => $userUuid,
                'total' => $total,
                'notes' => $notes,
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });
    }

    public function updateStatus(string $id, string $status): ?Order
    {
        return $this->repository->updateStatus($id, $status);
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function cancel(string $id): ?Order
    {
        return $this->repository->updateStatus($id, 'cancelled');
    }
}