<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DeveloperProfileController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'stats' => [
            'members' => User::count(),
            'posts' => Post::count(),
            'answers' => Comment::count(),
        ],
    ]);
})->name('home');

// Choix de la langue (accessible aussi aux visiteurs)
Route::post('/locale', LocaleController::class)->middleware('throttle:30,1')->name('locale.update');

Route::middleware('auth')->group(function () {
    // Fil d'actualité & exploration
    Route::get('/dashboard', [FeedController::class, 'index'])->name('dashboard');
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
    Route::get('/search', SearchController::class)->name('search');

    // Discussions
    Route::get('/my-posts', [PostController::class, 'mine'])->name('posts.mine');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,1')->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Réponses & likes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('throttle:20,1')->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/posts/{post}/like', LikeController::class)->middleware('throttle:60,1')->name('posts.like');

    // Développeurs
    Route::get('/developers', [DeveloperController::class, 'index'])->name('developers.index');
    Route::get('/developers/{user}', [DeveloperController::class, 'show'])->name('developers.show');

    // Compte & profil développeur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/developer', [DeveloperProfileController::class, 'update'])->name('profile.developer.update');
});

require __DIR__.'/auth.php';
