<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\Models\Cart;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        private CartRepository $repository
    ) {}

    public function getByUser(string $userUuid): ?Cart
    {
        return $this->repository->findByUserWithItems($userUuid);
    }

    public function getOrCreateCart(string $userUuid): Cart
    {
        return $this->repository->findOrCreateByUser($userUuid);
    }

    public function addItem(string $userUuid, string $productUuid, int $quantity): void
    {
        $cart = $this->repository->findOrCreateByUser($userUuid);
        $this->repository->addItem($cart->uuid, $productUuid, $quantity);
    }

    public function updateItemQuantity(string $cartItemUuid, int $quantity): void
    {
        $this->repository->updateItemQuantity($cartItemUuid, $quantity);
    }

    public function removeItem(string $cartItemUuid): bool
    {
        return $this->repository->removeItem($cartItemUuid);
    }

    public function clearCart(string $userUuid): void
    {
        $cart = $this->repository->findByUser($userUuid);
        if ($cart) {
            $this->repository->clearCart($cart->uuid);
        }
    }

    public function getTotal(string $userUuid): float
    {
        $cart = $this->repository->findByUserWithItems($userUuid);
        if (!$cart) {
            return 0;
        }

        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return $total;
    }
}