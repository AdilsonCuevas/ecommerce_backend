<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection($this->orderService->getAll());
    }

    public function show(string $id): JsonResponse|OrderResource
    {
        $id = base64_decode($id);
        $order = $this->orderService->getById($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
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