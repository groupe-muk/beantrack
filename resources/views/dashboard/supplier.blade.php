<div class="mt-8">
    <h2 class="text-xl font-semibold text-light-brown mb-4">Supplier Dashboard</h2>
    <div class="bg-white shadow rounded-lg p-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="border p-4 rounded-lg">
                <h3 class="font-medium text-coffee-brown mb-2">Inventory Management</h3>
                <p class="text-gray-600 mb-4">Manage your coffee bean inventory</p>
                <a href="#" class="text-light-brown hover:text-coffee-brown">View Inventory →</a>
            </div>
            <div class="border p-4 rounded-lg">
                <h3 class="font-medium text-coffee-brown mb-2">Shipments</h3>
                <p class="text-gray-600 mb-4">Track and manage coffee bean shipments</p>
                <a href="#" class="text-light-brown hover:text-coffee-brown">View Shipments →</a>
            </div>
        </div>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold text-light-brown mb-4">Recent Orders</h2>
    <div class="bg-white shadow rounded-lg p-4">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Order ID</th>
                        <th scope="col" class="px-6 py-3">Customer</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">#ORD-001</td>
                        <td class="px-6 py-4">Coffee Shop A</td>
                        <td class="px-6 py-4"><span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Shipped</span></td>
                        <td class="px-6 py-4">{{ now()->subDays(2)->format('Y-m-d') }}</td>
                    </tr>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">#ORD-002</td>
                        <td class="px-6 py-4">Coffee Shop B</td>
                        <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Processing</span></td>
                        <td class="px-6 py-4">{{ now()->subDays(1)->format('Y-m-d') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
