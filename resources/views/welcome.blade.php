@extends('layouts.app')

@section('content')
    
    <div class="absolute top-6 left-8 flex items-center border-b h-17 pr-3">
        <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-7 h-7">
        <h1 class="text-coffee-brown text-3xl font-semibold ml-2">BeanTrack</h1>
    </div>
    @guest
        
    
    <div class="absolute top-6 right-8 flex gap-4">
        <a href="{{ route('show.create') }}" class=" text-coffee-brown font-bold hover:text-light-brown">Register</a>
        <a href="{{ route('show.login') }}" class="btn text-white bg-light-brown px-8 py-1.5 rounded-full hover:bg-coffee-brown font-medium">Login</a>
        @endguest
        @auth
    <div class="absolute top-6 right-8 flex items-center">
        <span class="border-r-2 text-white h-6 mx-4">
         Hi,there <span class="text-white font-semibold">{{ auth()->user()->name ?? 'Guest' }}</span>!
        </span>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn">Logout</button>
        </form>
    </div>
        @endauth
    </div>

    
    <div class="flex min-h-screen">
        <div class="w-1/2 flex flex-col justify-center px-12">
            <p class="text-lg text-brown mb-4">A new simple way to manage your.......</p>
            <h1 class="text-7xl font-bold text-coffee-brown mb-4">COFFEE</h1>
            <h2 class="text-4xl font-semibold text-coffee-brown mb-8">Supply chain</h2>
            <div class="text-light-brown mb-8">
                <p>Made for suppliers, vendors and factories in coffee business.</p>
                <p>Tracking everything in the palm of your hand!</p>
            </div>
            <a href="{{ route('show.create') }}" class="text-white bg-coffee-brown font-semibold hover:bg-light-brown px-6 py-2.5 rounded-full">Get Started</a>
        </div>
        
        <div class="w-1/2 h-screen pt-24">
            <img src="{{ asset('/images/Welcome-page-image.png') }}" alt="coffee" class="w-full h-full object-cover" />
        </div>
    </div>
@endsection
