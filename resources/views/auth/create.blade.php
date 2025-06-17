@extends('layouts.app')
    @section('content')

        
    <form action = "{{ route('create') }}" method = "POST" >
        @csrf
        <div style="max-width: 400px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Create  your Account</h2>
    <div style="margin-bottom: 1rem;">
        <label for="name" style="display: block; margin-bottom: 0.5rem;">Name</label>
        <input type="text" id="name" name="name" required style="width: 100%; padding: 0.5rem;"  value="{{ old('name') }}">
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="email" style="display: block; margin-bottom: 0.5rem;">Email</label>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 0.5rem;" value="{{ old('email') }}">
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="password" style="display: block; margin-bottom: 0.5rem;">Password</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 0.5rem;">
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="password_confirmation" style="display: block; margin-bottom: 0.5rem;">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required style="width: 100%; padding: 0.5rem;">
    </div>
    <button type="submit" style="padding: 0.5rem 1rem;">Create Account</button>

    
</div>
    </form>

    <div style="text-align: center; margin-top: 1rem;">
        <a href="{{ route('login') }}" style="margin-right: 1rem;">Already have an account? Login</a>
        <a href="{{ route('create') }}">Create</a>
    </div>
    @if($errors->any())
        <ul class ="px-4 py-2 bg-red-100 ">
            @foreach($errors->all() as $error)
                <li class="my-2 text-red-500">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
@endsection