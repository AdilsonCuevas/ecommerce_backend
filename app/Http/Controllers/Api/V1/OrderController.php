<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $user = auth()->user();
        return OrderResource::collection($this->orderService->getByUser($user->uuid));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        $items = array_map(function ($item) {
            return [
                'product_uuid' => base64_decode($item['product_id']),
                'quantity' => $item['quantity'],
            ];
        }, $data['items']);

        $order = $this->orderService->create($user->uuid, $items, $data['notes'] ?? null);

        return response()->json([
            'data' => new OrderResource($order->load('items.product')),
            'message' => 'Order created successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse|OrderResource
    {
        $id = base64_decode($id);
        $user = auth()->user();
        $order = $this->orderService->getById($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->user_uuid !== $user->uuid && !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new OrderResource($order->load('items.product'));
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $id = base64_decode($id);
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $order = $this->orderService->updateStatus($id, $validated['status']);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Order status updated successfully',
        ]);
    }
}