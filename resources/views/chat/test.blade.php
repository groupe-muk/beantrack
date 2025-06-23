@extends('layouts.main-view')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Chat Test</h1>
    
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Send Test Message</h2>
        
        @if(isset($sent) && $sent)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Message sent successfully!
            </div>
        @endif
        
        @if(isset($error) && $error)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Error: {{ $error }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('chat.test') }}">
            @csrf
            <div class="mb-4">
                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1">Recipient</label>
                <select name="receiver_id" id="receiver_id" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Select Recipient --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="message" id="message" rows="3" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Test Message
                </button>
            </div>
        </form>
    </div>
    
    <div class="bg-white shadow rounded-lg p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Current User</h2>
        <div class="px-4 py-3 bg-gray-50 rounded-md">
            <p><strong>ID:</strong> {{ $currentUser->id }}</p>
            <p><strong>Name:</strong> {{ $currentUser->name }}</p>
            <p><strong>Email:</strong> {{ $currentUser->email }}</p>
            <p><strong>Role:</strong> {{ $currentUser->role }}</p>
        </div>
    </div>
</div>
@endsection
