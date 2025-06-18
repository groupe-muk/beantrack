@extends('layouts.main-view')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-semibold text-coffee-brown mb-6">{{ ucfirst(Auth::user()->role) }} Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- Analytics Cards -->
        <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 ">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <span class="text-2xl sm:text-3xl leading-none font-bold text-coffee-brown">
                    @if(Auth::user()->isAdmin())
                        {{ \App\Models\User::count() }}
                    @elseif(Auth::user()->isSupplier())
                        {{ \App\Models\Order::where('supplier_id', Auth::id())->count() }}
                    @elseif(Auth::user()->isVendor())
                        {{ \App\Models\Order::where('vendor_id', Auth::id())->count() }}
                    @endif
                    </span>
                    <h3 class="text-base font-normal text-gray-500">
                    @if(Auth::user()->isAdmin())
                        Total Users
                    @elseif(Auth::user()->isSupplier())
                        Orders Received
                    @elseif(Auth::user()->isVendor())
                        Orders Placed
                    @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-specific content -->
    @if(Auth::user()->isAdmin())
        @include('dashboard.admin')
    @elseif(Auth::user()->isSupplier())
        @include('dashboard.supplier')
    @elseif(Auth::user()->isVendor())
        @include('dashboard.vendor')
    @endif
</div>
@endsection
