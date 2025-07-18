<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Harga;
use App\Models\Customer;
use App\Models\Akademisi;
use App\Policies\HargaPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\AkademisiPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Harga::class => HargaPolicy::class,
        Customer::class => CustomerPolicy::class,
        Akademisi::class => AkademisiPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
