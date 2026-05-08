<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderRepository extends BaseRepository
{
    protected $model = Order::class;

    public function findByUser(string $userUuid): Collection
    {
        return $this->model->where('user_uuid', $userUuid)->get();
    }

    public function findWithItems(string $id): ?Order
    {
        return $this->model->with('items')->where('uuid', $id)->first();
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function updateStatus(string $id, string $status): ?Order
    {
        $model = $this->find($id);
        if ($model) {
            $model->update(['status' => $status]);
        }
        return $model;
    }
}