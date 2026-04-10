<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::latest()->paginate(10);

        return response()->json($categories, 200);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json($category, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::withCount('articles')->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        if ($category->articles_count > 0) {
            return response()->json([
                'message' => 'Category cannot be deleted because it is associated with articles',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
