<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Notification data (pending orders + recent payments) for the admin layout bell.
        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();

            if (!$user || !$user->hasRole('admin')) {
                $view->with('adminNotif', ['pending_count' => 0, 'recent_paid' => collect()]);
                return;
            }

            $view->with('adminNotif', [
                'pending_count' => Order::where('status', 'pending')->where('expires_at', '>', now())->count(),
                'recent_paid' => Order::with(['user', 'event'])
                    ->where('status', 'paid')
                    ->orderByDesc('paid_at')
                    ->limit(6)
                    ->get(),
            ]);
        });
    }
}
