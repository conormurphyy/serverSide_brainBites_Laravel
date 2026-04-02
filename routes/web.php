<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\BrainBotController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/brainbot', [PageController::class, 'brainbot'])->name('brainbot.page');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/contact-messages', [ContactMessageController::class, 'index'])->name('admin.contact-messages.index');
    Route::patch('/admin/contact-messages/{contactMessage}/resolve', [ContactMessageController::class, 'toggleResolved'])->name('admin.contact-messages.resolve');
});

require __DIR__.'/auth.php';
