
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> <!-- Added for ApexCharts -->
</head>
<body class="bg-gray-100 p-6 font-sans">
  <!-- Header Section -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Inventory Management</h1>
    <div>
      <button onclick="addItem()" class="bg-black text-white px-4 py-2 rounded">+ Add Item</button>
    </div>
  </div>
  <p class="text-gray-600 mb-4">Track and manage your inventory across all locations</p>
  
  <!-- Stats Section -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
      <p class="text-sm text-gray-500">Total Items</p>
      <p id="totalItems" class="text-2xl font-bold">0</p> <!-- Changed: dynamic -->
      <p class="text-green-500 text-sm" id="itemsChange"></p> <!-- Changed: dynamic -->
    </div>
    <div class="bg-white p-4 rounded shadow">
      <p class="text-sm text-gray-500">Low Stock Alerts</p>
      <p id="lowStock" class="text-2xl font-bold text-yellow-500">0</p> <!-- Changed: dynamic -->
      <p class="text-gray-400 text-sm">Items below minimum stock</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <p class="text-sm text-gray-500">Out of Stock</p>
      <p id="outStock" class="text-2xl font-bold text-red-500">0</p> <!-- Changed: dynamic -->
      <p class="text-gray-400 text-sm">Items requiring reorder</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
      <p class="text-sm text-gray-500">Total Value</p>
      <p id="totalValue" class="text-2xl font-bold">$0.00</p> <!-- Changed: dynamic -->
      <p class="text-gray-400 text-sm">Current inventory value</p>
    </div>
  </div>

  <!-- ApexCharts Section -->
  <div class="bg-white rounded shadow p-4 mb-6">
    <div id="inventoryChart" class="w-full"></div> <!-- Added: Chart container -->
  </div>

  <!-- Filters -->
  <div class="flex flex-col md:flex-row justify-between items-center mb-4">
    <input id="search" type="text" placeholder="Search by name or SKU..." class="border rounded px-4 py-2 mb-2 md:mb-0 md:mr-4 w-full md:w-1/3" />
    <select id="categoryFilter" class="border rounded px-4 py-2 mb-2 md:mb-0 md:mr-4">
      <option value="">All Categories</option>
      
    </select>
    <button class="border rounded px-4 py-2">More Filters</button>
  </div>

  <!-- Inventory Table -->
  <div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full table-auto text-sm">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2">SKU</th>
          <th class="px-4 py-2">Product Name</th>
          <th class="px-4 py-2">Category</th>
          <th class="px-4 py-2">Quantity</th>
          <th class="px-4 py-2">Unit Price</th>
          <th class="px-4 py-2">Location</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Supplier</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody id="inventoryTable">
        <!-- Dynamic rows -->
      </tbody>
    </table>
  </div>

  <script>
    // --- Interactive Search and Filter ---
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('search').addEventListener('input', function() {
        filterTable();
      });
      document.getElementById('categoryFilter').addEventListener('change', function() {
        filterTable();
      });
    });

    let inventoryData = [];

    async function loadInventory() {
      const response = await fetch('/api/inventory'); // Changed to Laravel endpoint
      inventoryData = await response.json();
      renderTable(inventoryData);
      updateStats(inventoryData);
      renderChart(inventoryData);
    }

    function renderTable(data) {
      const table = document.getElementById('inventoryTable');
      table.innerHTML = '';
      data.forEach(item => {
        table.innerHTML += `
          <tr class="border-t">
            <td class="px-4 py-2">${item.sku}</td>
            <td class="px-4 py-2">${item.name}</td>
            <td class="px-4 py-2">${item.category}</td>
            <td class="px-4 py-2">${item.quantity} <span class="text-xs text-gray-400">(Min: 15 | Max: 50)</span></td>
            <td class="px-4 py-2">$${item.unit_price.toFixed(2)}</td>
            <td class="px-4 py-2">${item.location}</td>
            <td class="px-4 py-2">
              <span class="${item.status === 'Low Stock' ? 'bg-yellow-100 text-yellow-800' : item.status === 'Out of Stock' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'} text-xs px-2 py-1 rounded-full">${item.status}</span>
            </td>
            <td class="px-4 py-2">${item.supplier}</td>
            <td class="px-4 py-2">...</td>
          </tr>
        `;
      });
    }

    function filterTable() {
      const search = document.getElementById('search').value.toLowerCase();
      const category = document.getElementById('categoryFilter').value;
      const filtered = inventoryData.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(search) || item.sku.toLowerCase().includes(search);
        const matchesCategory = !category || item.category === category;
        return matchesSearch && matchesCategory;
      });
      renderTable(filtered);
      updateStats(filtered);
      renderChart(filtered);
    }

    function updateStats(data) {
      document.getElementById('totalItems').textContent = data.length;
      document.getElementById('lowStock').textContent = data.filter(i => i.status === 'Low Stock').length;
      document.getElementById('outStock').textContent = data.filter(i => i.status === 'Out of Stock').length;
      const totalValue = data.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
      document.getElementById('totalValue').textContent = $${totalValue.toLocaleString(undefined, {minimumFractionDigits:2})};
      document.getElementById('itemsChange').textContent = "+2 from last week"; // Example static
    }

    // --- ApexCharts Bar Chart ---
    let chart;
    function renderChart(data) {
      const categories = [...new Set(data.map(i => i.category))];
      const series = categories.map(cat => {
        return {
          name: cat,
          data: [data.filter(i => i.category === cat).reduce((sum, i) => sum + i.quantity, 0)]
        };
      });
      const options = {
        chart: { type: 'bar', height: 250 },
        series: series,
        xaxis: { categories: ['Quantity by Category'] }
      };
      if (chart) {
        chart.updateOptions(options);
      } else {
        chart = new ApexCharts(document.querySelector("#inventoryChart"), options);
        chart.render();
      }
    }

    // --- Add Item (Demo) ---
    function addItem() {
      const item = {
        sku: 'NEW-001',
        name: 'New Item',
        category: 'Furniture',
        quantity: 10,
        unit_price: 100.00,
        location: 'Warehouse A',
        status: 'In Stock',
        supplier: 'Supplier X'
      };
      fetch('/api/inventory', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item)
      }).then(() => loadInventory());
    }

    window.onload = loadInventory;
  </script>
</body>
</html>