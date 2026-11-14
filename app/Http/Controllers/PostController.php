<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function mine(Request $request): Response
    {
        $posts = Post::query()
            ->forFeed($request->user())
            ->whereBelongsTo($request->user())
            ->latest()
            ->paginate(10)
            ->through(fn (Post $p) => $p->append('excerpt'));

        return Inertia::render('Posts/Mine', ['posts' => $posts]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Posts/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name', 'color']),
            'types' => Post::TYPES,
            'preset' => [
                'type' => in_array($request->query('type'), array_keys(Post::TYPES), true) ? $request->query('type') : 'question',
            ],
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post)->with('success', 'Votre publication est en ligne.');
    }

    public function show(Request $request, Post $post): Response
    {
        $user = $request->user();

        $post->load(['user:id,name', 'user.profile', 'category'])
            ->loadCount(['likes', 'comments'])
            ->append('content_html');
        $post->liked = $post->likes()->where('user_id', $user->id)->exists();

        $comments = $post->comments()
            ->with(['user:id,name', 'user.profile:id,user_id,avatar,headline,country'])
            ->oldest()
            ->get()
            ->map(fn (Comment $c) => [
                ...$c->append('content_html')->toArray(),
                'can' => [
                    'update' => $user->can('update', $c),
                    'delete' => $user->can('delete', $c),
                ],
            ]);

        return Inertia::render('Posts/Show', [
            'post' => $post,
            'comments' => $comments,
            'can' => [
                'update' => $user->can('update', $post),
                'delete' => $user->can('delete', $post),
            ],
            'related' => Post::query()
                ->where('category_id', $post->category_id)
                ->whereKeyNot($post->id)
                ->withCount('comments')
                ->latest()
                ->take(4)
                ->get(['id', 'title', 'type', 'created_at']),
        ]);
    }

    public function edit(Post $post): Response
    {
        Gate::authorize('update', $post);

        return Inertia::render('Posts/Edit', [
            'post' => $post->only(['id', 'title', 'content', 'type', 'category_id']),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'color']),
            'types' => Post::TYPES,
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post->update($request->validated());

        return redirect()->route('posts.show', $post)->with('success', 'Publication mise à jour.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.mine')->with('success', 'Publication supprimée.');
    }
}
