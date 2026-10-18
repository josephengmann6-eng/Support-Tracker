<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\EntryStatusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/board', [BoardController::class, 'index'])->name('board.index');
    Route::post('/board/entries/{entry}/status', [EntryStatusController::class, 'update'])->name('board.update');

    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::get('/handover', [HandoverController::class, 'index'])->name('handover.index');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Admin only: activities and team. Members can update statuses but nothing here.
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('activities', ActivityController::class)->except(['show', 'destroy']);
        Route::patch('activities/{activity}/toggle', [ActivityController::class, 'toggle'])->name('activities.toggle');

        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

require __DIR__.'/auth.php';
