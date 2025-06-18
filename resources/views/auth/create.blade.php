@extends('layouts.app')
    @section('content')
<div class="flex min-h-screen">
    <img src="{{ asset('/images/Register-page-image.jpg') }}" alt = "coffee" class = "w-1/2 h-full object-cover sticky top-0 left-0" >
        <div class ="w-1/2 flex items-start justify-center">
        <div class="w-full  flex flex-col items-left">
            <div class = "flex items-center justify-end border-b h-17 pr-3">
                <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-7 h-7">
                <h1 class = "text-coffee-brown text-3xl font-semibold ml-2">BeanTrack</h1>
            </div>
        
    <form action = "{{ route('create') }}" method = "POST" >
        @csrf
        <div style="max-width: 400px; margin: 0 auto;">
            <h2 class = "w-full text-center text-5xl mb-5 text-light-brown font-semibold">Register</h2>
            <p class="w-full text-center mb-5 text-brown">provide your name, email and a strong password</p>
    
    <div class="mb-5">
        <h2 class="text-xl font-semibold text-coffee-brown mb-2">Account Type: {{ ucfirst($role ?? 'admin') }}</h2>
        <p class="text-sm text-brown">Creating a new {{ ucfirst($role ?? 'admin') }} account</p>
    </div>
    <input type="hidden" name="role" value="{{ $role ?? 'admin' }}">
    
    <div style="margin-bottom: 1rem;">
        <label for="name" class="w-full text-coffee-brown font-semibold">Full name</label>
        <input type="text" id="name" name="name" required class="w-full h-10 rounded border-0.5"  value="{{ old('name') }}">
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="email" class="w-full text-coffee-brown font-semibold">Email</label>
        <input type="email" id="email" name="email" required class="w-full h-10 rounded border-0.5" value="{{ old('email') }}">
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="password" class="w-full text-coffee-brown font-semibold">Password</label>
        <input type="password" id="password" name="password" required class="w-full h-10 rounded border-0.5;">
    </div>
    <div style="margin-bottom: 2rem;">
        <label for="password_confirmation" class="w-full text-coffee-brown font-semibold">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full h-10 rounded border-0.5">
    </div>
    <button type="submit" class="w-full font-semibold bg-coffee-brown text-white hover:bg-light-brown rounded p-3">Create account</button>

    
</div>
    </form>

    <div class="w-full p-10  flex justify-center mt-auto  ">
        <a href="{{ route('login') }}" class="w-full text-center text-brown  ">Already have an account? Login</a>
       
    </div>
    @if($errors->any())
        <ul class ="px-4 py-2 bg-red-100 ">
            @foreach($errors->all() as $error)
                <li class="my-2 text-red-500">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
        </div>
        </div>

</div>
@endsection