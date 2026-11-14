<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'country' => ['nullable', 'string', 'max:80'],
            'skill' => ['nullable', 'string', 'max:30'],
        ]);

        $developers = User::query()
            ->with('profile')
            ->withCount('posts')
            ->when($filters['country'] ?? null, fn ($q, $c) => $q->whereHas('profile', fn ($p) => $p->where('country', $c)))
            ->when($filters['skill'] ?? null, function ($q, $s) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $s).'%';
                $q->whereHas('profile', fn ($p) => $p->where('skills', 'like', $like));
            })
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->paginate(12, ['id', 'name'])
            ->withQueryString();

        return Inertia::render('Developers/Index', [
            'developers' => $developers,
            'filters' => [
                'country' => $filters['country'] ?? '',
                'skill' => $filters['skill'] ?? '',
            ],
            'countries' => config('devlink.countries'),
        ]);
    }

    public function show(Request $request, User $user): Response
    {
        $user->load('profile')->loadCount(['posts', 'comments']);
        $likesReceived = \App\Models\Like::whereIn('post_id', $user->posts()->select('id'))->count();

        return Inertia::render('Developers/Show', [
            'developer' => $user->only(['id', 'name', 'created_at', 'posts_count', 'comments_count']) + ['profile' => $user->profile],
            'likesReceived' => $likesReceived,
            'isMe' => $request->user()->id === $user->id,
            'posts' => Post::query()
                ->forFeed($request->user())
                ->whereBelongsTo($user)
                ->latest()
                ->take(10)
                ->get()
                ->each->append('excerpt'),
        ]);
    }
}
