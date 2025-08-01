<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\VendorApplication' => 'App\Policies\VendorApplicationPolicy',
        'App\Models\SupplierApplication' => 'App\Policies\SupplierApplicationPolicy',
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\CustomerSegment' => 'App\Policies\SegmentPolicy',

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
