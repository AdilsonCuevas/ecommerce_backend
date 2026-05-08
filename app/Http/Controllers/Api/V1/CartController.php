<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function index(): JsonResponse|CartResource
    {
        $user = auth()->user();
        $cart = $this->cartService->getByUser($user->uuid);

        if (!$cart) {
            return response()->json(['data' => null, 'items' => [], 'total' => 0]);
        }

        return new CartResource($cart->load('items.product'));
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        $this->cartService->addItem(
            $user->uuid,
            base64_decode($data['product_id']),
            $data['quantity']
        );

        return response()->json(['message' => 'Item added to cart successfully']);
    }

    public function updateItem(Request $request, string $id): JsonResponse
    {
        $id = base64_decode($id);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $this->cartService->updateItemQuantity($id, $data['quantity']);

        return response()->json(['message' => 'Cart item updated successfully']);
    }

    public function removeItem(string $id): JsonResponse
    {
        $id = base64_decode($id);
        $deleted = $this->cartService->removeItem($id);

        if (!$deleted) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json(['message' => 'Item removed from cart successfully']);
    }

    public function clear(): JsonResponse
    {
        $user = auth()->user();
        $this->cartService->clearCart($user->uuid);

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}