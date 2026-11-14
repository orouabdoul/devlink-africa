<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(Post::TYPES))],
        ]);
        $user = $request->user();

        $posts = Post::query()
            ->forFeed($user)
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Post $p) => $p->append('excerpt'));

        return Inertia::render('Dashboard', [
            'posts' => $posts,
            'filters' => ['type' => $filters['type'] ?? null],
            'popularCategories' => Category::withCount('posts')->orderByDesc('posts_count')->take(6)->get(),
            'suggestedDevelopers' => User::query()
                ->whereKeyNot($user->id)
                ->with('profile')
                ->withCount('posts')
                ->orderByDesc('posts_count')
                ->take(4)
                ->get(['id', 'name']),
            'stats' => [
                'members' => User::count(),
                'posts' => Post::count(),
                'answers' => Comment::count(),
                'myPosts' => $user->posts()->count(),
            ],
            'profileCompletion' => $user->profile?->completion() ?? 0,
        ]);
    }
}
