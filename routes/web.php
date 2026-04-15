<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\BrainBotController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentVoteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\FollowingFeedController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('not_banned')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('home');
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/brainbot', [PageController::class, 'brainbot'])->name('brainbot.page');
    Route::get('/glossary', [PageController::class, 'glossary'])->name('glossary.page');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
    Route::get('/u/{user:username}', [UserProfileController::class, 'show'])->name('users.show');
    Route::post('/brainbot/chat', [BrainBotController::class, 'chat'])->middleware('throttle:30,1')->name('brainbot.chat');
    Route::get('/brainbot/history', [BrainBotController::class, 'history'])->middleware('throttle:60,1')->name('brainbot.history');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        Route::post('/posts/{post}/like', LikeController::class)->name('posts.like');
        Route::post('/posts/{post}/bookmark', BookmarkController::class)->name('posts.bookmark');
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/posts/{post}/comments/{comment}/upvote', CommentVoteController::class)->name('comments.upvote');
        Route::post('/users/{user}/follow', FollowController::class)->name('users.follow');
        Route::get('/following', FollowingFeedController::class)->name('following.index');
        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/admin/contact-messages', [ContactMessageController::class, 'index'])->name('admin.contact-messages.index');
        Route::patch('/admin/contact-messages/{contactMessage}/resolve', [ContactMessageController::class, 'toggleResolved'])->name('admin.contact-messages.resolve');
        Route::patch('/admin/posts/{post}/approve', [ContactMessageController::class, 'approvePost'])->name('admin.posts.approve');
        Route::patch('/admin/posts/{post}/reject', [ContactMessageController::class, 'rejectPost'])->name('admin.posts.reject');
        Route::patch('/admin/users/{user}/ban', [ContactMessageController::class, 'toggleBanUser'])->name('admin.users.ban');
        Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    });

    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
});

require __DIR__.'/auth.php';
