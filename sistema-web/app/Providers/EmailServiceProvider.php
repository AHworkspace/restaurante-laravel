<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EmailServiceInterface;
use App\Services\EmailService;
use App\Services\NativeEmailService;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar la implementación por defecto
        $this->app->bind(EmailServiceInterface::class, function ($app) {
            // Usar protocolos nativos por defecto
            return new NativeEmailService();
        });

        // También registrar el servicio original
        $this->app->bind(EmailService::class, EmailService::class);
        $this->app->bind(NativeEmailService::class, NativeEmailService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

