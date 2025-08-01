@extends('layouts.main-view')

@section('content')
<div class="p-4 bg-light-background dark:bg-dark-background">
    <h1 class="text-2xl font-semibold text-coffee-brown mb-6">{{ ucfirst(Auth::user()->role) }} Dashboard</h1>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

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
