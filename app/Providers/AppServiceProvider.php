<?php

namespace App\Providers;

use App\Contracts\ItemRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\ReceivingRepositoryInterface;
use App\Contracts\RequestRepositoryInterface;
use App\Contracts\StockOpnameRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\ItemRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\ReceivingRepository;
use App\Repositories\RequestRepository;
use App\Repositories\StockOpnameRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(RequestRepositoryInterface::class, RequestRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, PurchaseOrderRepository::class);
        $this->app->bind(ReceivingRepositoryInterface::class, ReceivingRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(StockOpnameRepositoryInterface::class, StockOpnameRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
