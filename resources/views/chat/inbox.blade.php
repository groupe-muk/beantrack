@extends('layouts.main-view')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">Inbox</h1>
    
    <!-- Search Bar -->
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input type="search" id="user-search" class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search users..." required>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400" id="tabLinks">
            <li class="mr-2">
                <a href="#" class="inline-flex items-center p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group active" id="suppliers-tab" data-tab="suppliers-tab-content">
                    Suppliers
                    <span class="bg-gray-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300 ml-2">{{ count($suppliers) }}</span>
                </a>
            </li>
            <li class="mr-2">
                <a href="#" class="inline-flex items-center p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group" id="vendors-tab" data-tab="vendors-tab-content">
                    Vendors
                    <span class="bg-gray-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300 ml-2">{{ count($vendors) }}</span>
                </a>
            </li>
            @if(isset($admins) && count($admins) > 0)
            <li>
                <a href="#" class="inline-flex items-center p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group" id="admins-tab" data-tab="admins-tab-content">
                    Admins
                    <span class="bg-gray-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300 ml-2">{{ count($admins) }}</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
    
    <!-- Tab Content -->
    <div id="tab-content">
        <!-- Suppliers Tab -->
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 tab-content active" id="suppliers-tab-content">
            <div class="grid gap-4">
                @foreach($suppliers as $supplier)
                <a href="{{ route('chat.room', $supplier->id) }}" class="block">
                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-lg">
                                {{ strtoupper(substr($supplier->name, 0, 1)) }}
                            </div>
                            <span class="bg-green-500 w-3 h-3 rounded-full absolute bottom-0 right-0 border-2 border-white"></span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-md font-semibold">{{ $supplier->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $supplier->email }}</p>
                            @php
                                $lastMessage = $recentMessages->firstWhere('contact_id', $supplier->id);
                            @endphp
                            @if($lastMessage)
                                <p class="text-xs text-gray-500 mt-1 truncate w-64">
                                    {{ \Carbon\Carbon::parse($lastMessage->created_at)->diffForHumans() }}: {{ $lastMessage->content }}
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
                
                @if(count($suppliers) === 0)
                <div class="p-4 text-center text-gray-500">
                    No suppliers found
                </div>
                @endif
            </div>
        </div>
        
        <!-- Vendors Tab -->
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 tab-content hidden" id="vendors-tab-content">
            <div class="grid gap-4">
                @foreach($vendors as $vendor)
                <a href="{{ route('chat.room', $vendor->id) }}" class="block">
                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-semibold text-lg">
                                {{ strtoupper(substr($vendor->name, 0, 1)) }}
                            </div>
                            <span class="bg-green-500 w-3 h-3 rounded-full absolute bottom-0 right-0 border-2 border-white"></span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-md font-semibold">{{ $vendor->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $vendor->email }}</p>
                            @php
                                $lastMessage = $recentMessages->firstWhere('contact_id', $vendor->id);
                            @endphp
                            @if($lastMessage)
                                <p class="text-xs text-gray-500 mt-1 truncate w-64">
                                    {{ \Carbon\Carbon::parse($lastMessage->created_at)->diffForHumans() }}: {{ $lastMessage->content }}
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
                
                @if(count($vendors) === 0)
                <div class="p-4 text-center text-gray-500">
                    No vendors found
                </div>
                @endif
            </div>
        </div>
        
        <!-- Admins Tab -->
        @if(isset($admins) && count($admins) > 0)
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 tab-content hidden" id="admins-tab-content">
            <div class="grid gap-4">
                @foreach($admins as $admin)
                <a href="{{ route('chat.room', $admin->id) }}" class="block">
                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-semibold text-lg">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <span class="bg-green-500 w-3 h-3 rounded-full absolute bottom-0 right-0 border-2 border-white"></span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-md font-semibold">{{ $admin->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                            @php
                                $lastMessage = $recentMessages->firstWhere('contact_id', $admin->id);
                            @endphp
                            @if($lastMessage)
                                <p class="text-xs text-gray-500 mt-1 truncate w-64">
                                    {{ \Carbon\Carbon::parse($lastMessage->created_at)->diffForHumans() }}: {{ $lastMessage->content }}
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const tabLinks = document.querySelectorAll("#tabLinks a");
    const tabContents = document.querySelectorAll(".tab-content");
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            tabLinks.forEach(tab => {
                tab.classList.remove('active', 'text-blue-600', 'border-blue-600');
            });
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active', 'text-blue-600', 'border-blue-600');
            
            // Show corresponding content
            const tabId = this.getAttribute('data-tab');
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.remove('hidden');
                tabContent.classList.add('active');
            }
        });
    });
    
    // User search functionality
    const userSearch = document.getElementById('user-search');
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userCards = document.querySelectorAll('#tab-content a');
            
            userCards.forEach(card => {
                const userName = card.querySelector('h3').textContent.toLowerCase();
                const userEmail = card.querySelector('p').textContent.toLowerCase();
                
                if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
