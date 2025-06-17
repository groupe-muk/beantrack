<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>
        <link rel="icon" href="{{ asset('images/logo/beantrack-color-logo.png') }}" type="image/png">

       <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
       <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class = "font-serif">
    <!-- Navigation Bar -->
    

<nav class="bg-white border-gray-200 dark:bg-gray-900">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
    <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="/images/logo/beantrack-color-logo.PNG" class="h-8" alt="Flowbite Logo" />
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">BeanTrack</span>
    </a>
    <button data-collapse-toggle="navbar-default" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-default" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
    </button>
    <div class="hidden w-full md:block md:w-auto" id="navbar-default">
      <span class=" justify-center font-medium flex flex-row p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
        
          
          <a href="#" class="justify-center block py-2 px-3 text-gray-500 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-gray-500 md:p-0 dark:text-white mt-2.5 ">
          <svg class=" inline w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd" d="M12 20a7.966 7.966 0 0 1-5.002-1.756l.002.001v-.683c0-1.794 1.492-3.25 3.333-3.25h3.334c1.84 0 3.333 1.456 3.333 3.25v.683A7.966 7.966 0 0 1 12 20ZM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 5.5-4.44 9.963-9.932 10h-.138C6.438 21.962 2 17.5 2 12Zm10-5c-1.84 0-3.333 1.455-3.333 3.25S10.159 13.5 12 13.5c1.84 0 3.333-1.455 3.333-3.25S13.841 7 12 7Z" clip-rule="evenodd"/>
</svg>
<p class="inline">Guest</p></a>
          <a href="#" class="btn block py-2 px-3 text-gray-500 rounded-sm  md:border-0  md:p-0 dark:text-white md:dark:hover:text-gray-500 dark:hover:bg-gray-500 dark:hover:text-white md:dark:hover:bg-transparent">Logout</a>
      </span>
    </div>
  </div>
</nav>

    <!-- Header section -->
    <section class="text-center py-16 px-6 max-w-3xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Track your coffee beans from farm to cup</h1>
        <p class="text-gray-600 text-base md:text-lg">Manage your coffee bean supply chain efficiently and transparently with BeanTrack.</p>
    </section>

    <!-- Features Section -->
    <section class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Admin Card -->
       <a href="#"> <!--handle routing here-->
       <div class="  cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/landing-page-image-1.jpg" alt="Admin" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Admin</h3>
                <p class="text-sm text-gray-600 mt-2">Manage users, roles, and system settings.</p>
            </div>
        </div>
        </a>

        <!-- Supplier Card -->
        <a href="#"> <!--handle routing here-->
        <div class="cursor-pointer transition hover:scale-110 shadow rounded-lg overflow-hidden text-center ">
            <img src="/images/landing-page-image-2.jpg" alt="Supplier" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Supplier</h3>
                <p class="text-sm text-gray-600 mt-2">Track bean shipments, update inventory, and manage orders.</p>
            </div>
        </div>
        </a>

        <!-- Vendor Card -->
        <a href="#"> <!--handle routing here-->
        <div class=" cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/landing-page-image-3.jpg" alt="Vendor" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Vendor</h3>
                <p class="text-sm text-gray-600 mt-2">View inventory, place orders, and track deliveries.</p>
            </div>
        </div>
        </a>
    </section>



        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

 </body>
 </html>   

