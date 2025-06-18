<div class="mt-8">
    <h2 class="text-xl font-semibold text-light-brown mb-4">Admin Dashboard</h2>
    <div class="bg-white shadow rounded-lg p-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="border p-4 rounded-lg">
                <h3 class="font-medium text-coffee-brown mb-2">User Management</h3>
                <p class="text-gray-600 mb-4">Manage user accounts and permissions</p>
                <a href="#" class="text-light-brown hover:text-coffee-brown">View Users →</a>
            </div>
            <div class="border p-4 rounded-lg">
                <h3 class="font-medium text-coffee-brown mb-2">System Settings</h3>
                <p class="text-gray-600 mb-4">Configure application settings and preferences</p>
                <a href="#" class="text-light-brown hover:text-coffee-brown">Go to Settings →</a>
            </div>
        </div>
    </div>
</div>

<div class="mt-8">
    <h2 class="text-xl font-semibold text-light-brown mb-4">Recent Activity</h2>
    <div class="bg-white shadow rounded-lg p-4">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">User</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">John Doe</td>
                        <td class="px-6 py-4">Registered</td>
                        <td class="px-6 py-4">{{ now()->subDays(2)->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">Jane Smith</td>
                        <td class="px-6 py-4">Updated Profile</td>
                        <td class="px-6 py-4">{{ now()->subDays(1)->format('Y-m-d H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
