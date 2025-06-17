@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-white">
    <img src="{{ asset('/images/Login-page-image.jpg') }}" alt = "coffee" class = "w-1/2 h-full object-cover sticky top-0 left-0" >
        <div class ="w-1/2 flex items-start justify-center">
        <div class="w-full p flex flex-col items-left">
            <div class = "flex items-center justify-end border-b h-17 pr-3">
                <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-7 h-7">
                <h1 class = "text-coffee-brown text-3xl font-semibold ml-2">BeanTrack</h1>
            </div>
            <div class = "m-10">
            <h1 class = "w-full text-left text-5xl mt-15 mb-5 text-light-brown font-semibold">Login</h1>
            <p class ="w-full mb-10 text-brown">Log in with the data entered during registration</p>
        <form method="POST" action="{{ route('login') }}" class = "w-full">
        
          @csrf
            <div style="margin-bottom: 1.5rem;">
                <label for="email" class= "block mb-2 text-coffee-brown font-semibold">Email</label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}" value="{{ old('email') }}" class = "w-full border-soft-gray rounded border-2 h-12">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="password" class = "block mb-2 text-coffee-brown font-semibold">Password</label>
                <input type="password" id="password" name="password" required class = "w-full border-soft-gray rounded border-2 h-12">
            </div>
            <button type="submit" class = "bg-coffee-brown text-white w-full rounded p-3 font-semibold mt-5">
                Login
            </button>
        </form>
        
            
       
        

<div class="w-full p-10 mt-25 border border-light-gray rounded-lg shadow-sm ">
        <h5 class="mb-5 text-2xl font-semibold tracking-tight text-light-brown">Register</h5>
    <p class="mb-10 font-normal text-brown">If you are new to BeanTrack, register here.</p>
    <a href="{{ route('show.create') }}" class="flex items-center justify-center px-3 py-2 h-15 font-bold text-center text-coffee-brown bg-light-gray rounded hover:bg-soft-gray">
        Create Account

    </a>
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
</div>
@endsection