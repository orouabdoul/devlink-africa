<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
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
        $post = $request->user()->posts()->create($request->safe()->only(['title', 'content', 'type', 'category_id']));
        Attachment::storeImages($request->file('images', []), $request->user(), ['post_id' => $post->id]);
        Attachment::storeDocuments($request->file('documents', []), $request->user(), ['post_id' => $post->id]);

        return redirect()->route('posts.show', $post)->with('success', __('Votre publication est en ligne.'));
    }

    public function show(Request $request, Post $post): Response
    {
        $user = $request->user();

        $post->load(['user:id,name', 'user.profile', 'category', 'attachments'])
            ->loadCount(['likes', 'comments'])
            ->append('content_html');
        $post->liked = $post->likes()->where('user_id', $user->id)->exists();

        $comments = $post->comments()
            ->with(['user:id,name', 'user.profile:id,user_id,avatar,headline,country', 'attachments'])
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
            'attachments' => $post->attachments()->get(),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'color']),
            'types' => Post::TYPES,
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        // Seules les pièces jointes de CETTE publication peuvent être retirées.
        $remove = $post->attachments()->whereIn('id', array_map('intval', $request->input('remove_images', [])))->get(['id', 'kind']);
        $count = fn (string $kind) => $post->attachments()->where('kind', $kind)->count() - $remove->where('kind', $kind)->count();
        if ($count('image') + count($request->file('images', [])) > Attachment::MAX_PER_POST) {
            throw ValidationException::withMessages([
                'images' => __('Une publication peut contenir au maximum :max captures.', ['max' => Attachment::MAX_PER_POST]),
            ]);
        }
        if ($count('document') + count($request->file('documents', [])) > Attachment::MAX_DOCUMENTS_PER_POST) {
            throw ValidationException::withMessages([
                'documents' => __('Une publication peut contenir au maximum :max documents.', ['max' => Attachment::MAX_DOCUMENTS_PER_POST]),
            ]);
        }
        $remove = $remove->pluck('id');

        $post->update($request->safe()->only(['title', 'content', 'type', 'category_id']));
        if ($remove->isNotEmpty()) {
            Attachment::purgeFiles(Attachment::whereIn('id', $remove));
            Attachment::whereIn('id', $remove)->delete();
        }
        Attachment::storeImages($request->file('images', []), $request->user(), ['post_id' => $post->id]);
        Attachment::storeDocuments($request->file('documents', []), $request->user(), ['post_id' => $post->id]);

        return redirect()->route('posts.show', $post)->with('success', __('Publication mise à jour.'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.mine')->with('success', __('Publication supprimée.'));
    }
}
