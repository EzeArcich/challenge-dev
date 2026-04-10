<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private SlugService $slugService
    ) {}

    public function index(): JsonResponse
    {
        $articles = Article::with(['author', 'categories'])
            ->latest()
            ->paginate(10);

        return response()->json($articles, 200);
    }

    public function show(int $id): JsonResponse
    {
        $article = Article::with(['author', 'categories'])->find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        return response()->json($article, 200);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Only active users can create articles',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'slug' => $this->slugService->generateUnique($validated['title']),
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'user_id' => $request->user()->id,
        ]);

        $article->categories()->sync($validated['category_ids']);

        $article->load(['author', 'categories']);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Only active users can edit articles',
            ], 403);
        }

        $article = Article::with(['author', 'categories'])->find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
            'category_ids' => ['sometimes', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        if (isset($validated['title'])) {
            $article->title = $validated['title'];
            $article->slug = $this->slugService->generateUnique($validated['title'], $article->id);
        }

        if (isset($validated['content'])) {
            $article->content = $validated['content'];
        }

        if (isset($validated['status'])) {
            $article->status = $validated['status'];

            if ($validated['status'] === 'published' && ! $article->published_at) {
                $article->published_at = now();
            }

            if ($validated['status'] === 'draft') {
                $article->published_at = null;
            }
        }

        $article->save();

        if (array_key_exists('category_ids', $validated)) {
            $article->categories()->sync($validated['category_ids']);
        }

        $article->load(['author', 'categories']);

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $article,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $article = Article::find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ], 200);
    }
}
