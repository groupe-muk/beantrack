@extends('layouts.main-view')

@section('content')
<div class="p-4 bg-light-background">
    <h1 class="text-2xl font-semibold text-coffee-brown mb-6">{{ ucfirst(Auth::user()->role) }} Dashboard</h1>

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
