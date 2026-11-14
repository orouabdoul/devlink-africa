<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $q = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? ''));

        $posts = null;
        $developers = collect();

        if ($q !== '') {
            $posts = Post::query()
                ->forFeed($request->user())
                ->search($q)
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Post $p) => $p->append('excerpt'));

            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $developers = User::query()
                ->with('profile')
                ->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhereHas('profile', fn ($p) => $p->where('skills', 'like', $like)
                        ->orWhere('headline', 'like', $like)
                        ->orWhere('country', 'like', $like)))
                ->take(6)
                ->get(['id', 'name']);
        }

        return Inertia::render('Search', [
            'q' => $q,
            'posts' => $posts,
            'developers' => $developers,
        ]);
    }
}
