<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
        <meta name="user-id" content="{{ auth()->user()->id }}">
        <meta name="user-name" content="{{ auth()->user()->name }}">
        <meta name="user-email" content="{{ auth()->user()->email }}">
        @endauth

        <title>{{ config('app.name') }}</title>
        <link rel="icon" href="{{ asset('images/logo/beantrack-color-logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
         {{-- For chat --}}
        <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
        @stack('styles')
    </head>

    <body class = "font-serif">        
    <x-partials.navbar-dashboard/>
        <x-partials.sidebar :menuItems="\App\Helpers\MenuHelper::getMenuItems(auth()->user()->role ?? null)"/>

        <div id="main-content" class="relative w-full h-full overflow-y-auto bg-gray-50 lg:pl-64 dark:bg-gray-900 pt-16">

        {{-- Charts, KPIs go here --}}
     @yield('content')  

   
  </div>
       
    
        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

        @if(auth()->check())
        <script>
            // Function to update unread message count
            function updateUnreadCount() {
                fetch('{{ route('chat.unread') }}')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('unread-message-count');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching unread count:', error));
            }

            // Update on page load
            document.addEventListener('DOMContentLoaded', function() {
                updateUnreadCount();
                // Update every 30 seconds
                setInterval(updateUnreadCount, 30000);
            });

            // Listen for message events (if using pusher)
            @if(config('broadcasting.connections.pusher.key'))
            window.addEventListener('message-received', function() {
                updateUnreadCount();
            });
            @endif
        </script>
        @endif

        @stack('scripts')
    </body>

   
</html>
