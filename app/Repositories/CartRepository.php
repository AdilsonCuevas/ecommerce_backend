<?php

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Support\Collection;

class CartRepository extends BaseRepository
{
    protected $model = Cart::class;

    public function findByUser(string $userUuid): ?Cart
    {
        return $this->model->where('user_uuid', $userUuid)->first();
    }

    public function findByUserWithItems(string $userUuid): ?Cart
    {
        return $this->model->with('items.product')->where('user_uuid', $userUuid)->first();
    }

    public function create(string $userUuid): Cart
    {
        return $this->model->create(['user_uuid' => $userUuid]);
    }

    public function findOrCreateByUser(string $userUuid): Cart
    {
        return $this->findByUser($userUuid) ?? $this->create($userUuid);
    }

    public function addItem(string $cartUuid, string $productUuid, int $quantity): void
    {
        $cart = $this->find($cartUuid);
        $item = $cart->items()->where('product_uuid', $productUuid)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_uuid' => $productUuid,
                'quantity' => $quantity,
            ]);
        }
    }

    public function updateItemQuantity(string $cartItemUuid, int $quantity): void
    {
        $item = \App\Models\CartItem::where('uuid', $cartItemUuid)->first();
        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->update(['quantity' => $quantity]);
            }
        }
    }

    public function removeItem(string $cartItemUuid): bool
    {
        $item = \App\Models\CartItem::where('uuid', $cartItemUuid)->first();
        return $item ? $item->delete() : false;
    }

    public function clearCart(string $cartUuid): void
    {
        $cart = $this->find($cartUuid);
        $cart?->items()->delete();
    }
}