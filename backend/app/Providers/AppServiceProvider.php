<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StoreRepositoryInterface;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Listeners\LogOrderActivity;
use App\Listeners\NotifyStoreViaWhatsApp;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Repositories\EloquentCategoryRepository;
use App\Repositories\EloquentOrderRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\EloquentStoreRepository;
use App\Services\Payment\MercadoPagoPaymentService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interfaces
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, EloquentStoreRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);

        // Register Payment Service as singleton
        $this->app->singleton(PaymentService::class, function ($app) {
            $service = new PaymentService();
            $service->registerGateway(new StripePaymentService());
            $service->registerGateway(new MercadoPagoPaymentService());
            return $service;
        });
    }

    public function boot(): void
    {
        // Register Observers
        Order::observe(OrderObserver::class);

        // Register Event Listeners
        Event::listen(OrderCreated::class, NotifyStoreViaWhatsApp::class);
        Event::listen(OrderStatusChanged::class, LogOrderActivity::class);
    }
}
