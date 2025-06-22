<?php

namespace App\Providers;

use App\Services\Contracts\InvoiceLineServiceInterface;
use App\Factories\Contracts\SalesInvoiceFactoryInterface;
use App\Factories\SalesInvoiceFactory;
use App\Services\InvoiceLineService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InvoiceLineServiceInterface::class, InvoiceLineService::class);
        $this->app->bind(SalesInvoiceFactoryInterface::class, SalesInvoiceFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
