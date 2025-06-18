<div class="min-h-screen bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Welcome to BeanTrack</h1>
            <p class="text-xl text-gray-300">Select your role to get started</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Admin Card -->
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all hover:scale-105">
                <div class="h-48 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1498804103079-b09b9b5efc0f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-4">Admin</h3>
                    <p class="text-gray-300 mb-6">Manage users, roles, and system settings.</p>
                    <button wire:click="selectRole('admin')" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Select Admin
                    </button>
                </div>
            </div>

            <!-- Supplier Card -->
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all hover:scale-105">
                <div class="h-48 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1521017432531-fbd92d768814?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2">Supplier</h3>
                    <p class="text-gray-300 mb-6">Track bean shipments, update inventory, and manage orders.</p>
                    <button wire:click="selectRole('supplier')" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Select Supplier
                    </button>
                </div>
            </div>

            <!-- Vendor Card -->
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all hover:scale-105">
                <div class="h-48 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1495474475677-7a9dd0cc4c8d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');">
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2">Vendor</h3>
                    <p class="text-gray-300 mb-6">View inventory, place orders, and track deliveries.</p>
                    <button wire:click="selectRole('vendor')" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Select Vendor
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>