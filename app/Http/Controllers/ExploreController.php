<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'type' => ['nullable', Rule::in(array_keys(Post::TYPES))],
            'sort' => ['nullable', Rule::in(['recent', 'popular', 'unanswered'])],
        ]);
        $sort = $filters['sort'] ?? 'recent';

        $posts = Post::query()
            ->forFeed($request->user())
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($sort === 'popular', fn ($q) => $q->orderByDesc('likes_count')->orderByDesc('comments_count'))
            ->when($sort === 'unanswered', fn ($q) => $q->doesntHave('comments'))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Post $p) => $p->append('excerpt'));

        return Inertia::render('Explore', [
            'posts' => $posts,
            'categories' => Category::withCount('posts')->orderBy('name')->get(),
            'filters' => [
                'category' => $filters['category'] ?? null,
                'type' => $filters['type'] ?? null,
                'sort' => $sort,
            ],
        ]);
    }
}
