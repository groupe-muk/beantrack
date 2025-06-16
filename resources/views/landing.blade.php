
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BeanTrack Landing Page</title>
    @vite('resources/css/app.css') 
</head>
<body class="bg-white text-gray-800">

    <!-- Navbar -->
    <nav class="flex justify-between items-center py-4 px-8 shadow-md">
        <div class="flex items-center space-x-2">
            <img src="/logo.png" alt="BeanTrack Logo" class="h-8 w-8"> <!-- Replace with actual logo path -->
            <span class="text-xl font-semibold">BeanTrack</span>
        </div>
        <ul class="flex space-x-6 items-center">
            <li><a href="#" class="text-sm hover:underline">About</a></li>
            <li><a href="#" class="text-sm hover:underline">Guest</a></li>
            <li>
                <button class="bg-amber-800 hover:bg-amber-700 text-white text-sm px-4 py-2 rounded-full">Log Out</button>
            </li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="text-center py-16 px-6 max-w-3xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Track your coffee beans from farm to cup</h1>
        <p class="text-gray-600 text-base md:text-lg">Manage your coffee bean supply chain efficiently and transparently with BeanTrack.</p>
    </section>

    <!-- Features Section -->
    <section class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Admin Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/admin.jpg" alt="Admin" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Admin</h3>
                <p class="text-sm text-gray-600 mt-2">Manage users, roles, and system settings.</p>
            </div>
        </div>

        <!-- Supplier Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/supplier.jpg" alt="Supplier" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Supplier</h3>
                <p class="text-sm text-gray-600 mt-2">Track bean shipments, update inventory, and manage orders.</p>
            </div>
        </div>

        <!-- Vendor Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/vendor.jpg" alt="Vendor" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Vendor</h3>
                <p class="text-sm text-gray-600 mt-2">View inventory, place orders, and track deliveries.</p>
            </div>
        </div>
    </section>

</body>
</html>