<?php

namespace App\Providers;

use App\Support\PencatatAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, function (Login $event): void {
            PencatatAudit::log('auth_login', "Login berhasil: {$event->user->email}");
        });

        Event::listen(Failed::class, function (Failed $event): void {
            PencatatAudit::log('auth_login_failed', 'Login gagal: '.($event->credentials['email'] ?? '-'));
        });

        Event::listen(Logout::class, function (Logout $event): void {
            PencatatAudit::log('auth_logout', 'Logout: '.($event->user?->email ?? '-'));
        });
    }
}
