<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categoryService->getAll());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Category created successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse|CategoryResource
    {
        $id = base64_decode($id);
        $category = $this->categoryService->getById($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $id = base64_decode($id);
        $category = $this->categoryService->update($id, $request->validated());

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Category updated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $id = base64_decode($id);
        $deleted = $this->categoryService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json(['message' => 'Category deleted successfully']);
    }
}