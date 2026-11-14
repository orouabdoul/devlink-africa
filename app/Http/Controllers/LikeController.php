<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /** Bascule le « J'aime » : un second clic retire le like. */
    public function __invoke(Request $request, Post $post): RedirectResponse
    {
        $existing = Like::where('user_id', $request->user()->id)->where('post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            Like::firstOrCreate(['user_id' => $request->user()->id, 'post_id' => $post->id]);
        }

        return back();
    }
}
