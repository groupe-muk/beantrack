@extends('layouts.app')

@section('content')
    <div style="max-width: 400px; margin: 2rem auto;">
        <h2 style="text-align: center; margin-bottom: 1.5rem;">Login to Your Account</h2>
        <form method="POST" action="{{ route('login') }}" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}" style="width: 100%; padding: 0.5rem;" value="{{ old('email') }}">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.5rem;">
            </div>
            <button type="submit" style="width: 100%; padding: 0.75rem; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-weight: 600;">
                Login
            </button>
        </form>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="{{ route('show.create') }}">Don't have an account? Create</a>
        </div>
         @if($errors->any())
        <ul class ="px-4 py-2 bg-red-100 ">
            @foreach($errors->all() as $error)
                <li class="my-2 text-red-500">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    </div>
@endsection