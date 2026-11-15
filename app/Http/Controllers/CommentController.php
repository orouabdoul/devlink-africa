<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $comment = new Comment($request->safe()->only(['content']));
        $comment->user()->associate($request->user());
        $post->comments()->save($comment);
        Attachment::storeImages($request->file('images', []), $request->user(), ['comment_id' => $comment->id]);
        Attachment::storeDocuments($request->file('documents', []), $request->user(), ['comment_id' => $comment->id]);

        return back()->with('success', __('Réponse publiée.'));
    }

    public function update(StoreCommentRequest $request, Comment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);

        $comment->update($request->safe()->only(['content']));

        return back()->with('success', __('Réponse modifiée.'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', __('Réponse supprimée.'));
    }
}
