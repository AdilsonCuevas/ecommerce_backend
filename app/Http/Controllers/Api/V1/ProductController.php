<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Psy\Readline\Hoa\Console;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->productService->getCatalog());
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['category_uuid'] = $data['category_id'];
        unset($data['category_id']);

        $product = $this->productService->create($data);

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Product created successfully',
        ], 201);
    }

    public function show(string $id)
    {
        $id = base64_decode($id);
        $product = $this->productService->getById($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return $product;
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $id = base64_decode($id);
        $data = $request->validated();

        if (isset($data['category_id'])) {
            $data['category_uuid'] = $data['category_id'];
            unset($data['category_id']);
        }

        $product = $this->productService->update($id, $data);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $id = base64_decode($id);
        $deleted = $this->productService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['message' => 'Product deleted successfully']);
    }
}