<?php

namespace App\Providers;

use App\Models\DailyEntry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Notification bell badge: overdue (pending 2+ days) items.
        View::composer('layouts.app', function ($view) {
            $view->with('overdueCount', auth()->check() ? DailyEntry::overdueCount() : 0);
        });
    }
}
