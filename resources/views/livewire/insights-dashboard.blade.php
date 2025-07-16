<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-coffee-brown dark:text-white">Customer Insights</h2>
            <p class="text-warm-gray dark:text-gray-400">Wholesaler segmentation based on purchase behavior and order patterns</p>
            <p class="text-sm text-warm-gray dark:text-gray-400 mt-1">Last updated: {{ $lastUpdated }}</p>
        </div>
        <button wire:click="refreshData" class="bg-light-brown hover:bg-coffee-brown text-white px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh Data
        </button>
    </div>

    <!-- Segment Legend -->
    <div class="bg-white p-4 rounded-2xl shadow-md mb-6">
        <h3 class="text-lg font-semibold text-coffee-brown mb-3">Segment Definitions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- RFM Segments Legend -->
            <div>
                <h4 class="font-medium text-coffee-brown mb-2">RFM Segments (Recency, Frequency, Monetary)</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                        <span class="font-medium">VIP:</span>
                        <span class="ml-1 text-warm-gray">Recent buyers, high frequency & value</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                        <span class="font-medium">Steady:</span>
                        <span class="ml-1 text-warm-gray">Consistent customers with good patterns</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-yellow-500 mr-2"></div>
                        <span class="font-medium">Growth:</span>
                        <span class="ml-1 text-warm-gray">Potential customers to nurture</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-orange-500 mr-2"></div>
                        <span class="font-medium">At-Risk:</span>
                        <span class="ml-1 text-warm-gray">Declining engagement, needs attention</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-red-500 mr-2"></div>
                        <span class="font-medium">Dormant:</span>
                        <span class="ml-1 text-warm-gray">Inactive customers requiring re-engagement</span>
                    </div>
                </div>
            </div>

            <!-- Order Size Segments Legend -->
            <div>
                <h4 class="font-medium text-coffee-brown mb-2">Order Size Segments</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-light-brown mr-2"></div>
                        <span class="font-medium">Bulk Buyers:</span>
                        <span class="ml-1 text-warm-gray">Large orders (≥1,000 kg average)</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-soft-brown mr-2"></div>
                        <span class="font-medium">Mid-Volume:</span>
                        <span class="ml-1 text-warm-gray">Medium orders (250-999 kg average)</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full bg-cream-brown mr-2"></div>
                        <span class="font-medium">Micro-orders:</span>
                        <span class="ml-1 text-warm-gray">Small orders (<250 kg average)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Total Wholesalers Summary -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h3 class="text-lg font-semibold text-coffee-brown dark:text-white mb-2">Total Wholesalers</h3>
            <div class="text-3xl font-bold text-light-brown">{{ $totalWholesalers }}</div>
            <p class="text-warm-gray dark:text-gray-400 text-sm mt-1">Active wholesalers in system</p>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h3 class="text-lg font-semibold text-coffee-brown dark:text-white mb-2">Segmentation Coverage</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-warm-gray">RFM Segments:</span>
                    <span class="font-semibold">{{ $segmentStats['rfm']->sum('count') }} assigned</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-warm-gray">Order Size Segments:</span>
                    <span class="font-semibold">{{ $segmentStats['order_size']->sum('count') }} assigned</span>
                </div>
                <div class="text-xs text-warm-gray mt-2">
                    Based on last 90 days of order activity
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- RFM Segments Pie Chart -->
        <div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full">
            <div class="flex items-center justify-between mb-4">
                <h5 class="text-xl font-bold leading-none text-coffee-brown dark:text-white">RFM Segment Distribution</h5>
                <div class="group relative">
                    <svg class="w-5 h-5 text-warm-gray cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="absolute right-0 top-6 w-64 bg-gray-800 text-white text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        RFM analysis segments customers based on:<br>
                        <strong>Recency:</strong> Days since last order<br>
                        <strong>Frequency:</strong> Number of orders (90 days)<br>
                        <strong>Monetary:</strong> Total order value (90 days)
                    </div>
                </div>
            </div>
            <div id="rfm-pie-chart" class="w-full h-full"></div>
        </div>

        <!-- Order Size Segments Bar Chart -->
        <div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full">
            <div class="flex items-center justify-between mb-4">
                <h5 class="text-xl font-bold leading-none text-coffee-brown dark:text-white">Order Size Distribution</h5>
                <div class="group relative">
                    <svg class="w-5 h-5 text-warm-gray cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="absolute right-0 top-6 w-64 bg-gray-800 text-white text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        Order size segments based on average quantity per order over the last 90 days. Helps identify bulk buyers vs. frequent small-order customers.
                    </div>
                </div>
            </div>
            <div id="order-size-bar-chart" class="w-full h-full"></div>
        </div>
    </div>

    <!-- Wholesaler Details Table -->
    <div class="mt-6 bg-white rounded-2xl shadow-md">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-coffee-brown">Wholesaler Segment Details</h3>
                    <p class="text-warm-gray text-sm mt-1">Individual wholesaler assignments and performance metrics</p>
                </div>
                
                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- RFM Filter -->
                    <select wire:model.live="rfmFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-brown focus:border-transparent">
                        <option value="">All RFM Segments</option>
                        <option value="VIP">VIP</option>
                        <option value="Steady">Steady</option>
                        <option value="Growth">Growth</option>
                        <option value="At-Risk">At-Risk</option>
                        <option value="Dormant">Dormant</option>
                    </select>
                    
                    <!-- Order Size Filter -->
                    <select wire:model.live="orderSizeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-brown focus:border-transparent">
                        <option value="">All Order Sizes</option>
                        <option value="Bulk Buyers">Bulk Buyers</option>
                        <option value="Mid-Volume">Mid-Volume</option>
                        <option value="Micro-orders">Micro-orders</option>
                    </select>
                    
                    <!-- Clear Filters -->
                    <button wire:click="clearFilters" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('name')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-coffee-brown">
                                <span>Wholesaler</span>
                                @if($sortField === 'name')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('distribution_region')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-coffee-brown">
                                <span>Region</span>
                                @if($sortField === 'distribution_region')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            RFM Segment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Performance
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-coffee-brown">
                                <span>Joined</span>
                                @if($sortField === 'created_at')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($wholesalerDetails as $wholesaler)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-light-brown flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                {{ substr($wholesaler['name'], 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $wholesaler['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $wholesaler['email'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $wholesaler['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $wholesaler['region'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $rfmColors = [
                                        'VIP' => 'bg-green-100 text-green-800',
                                        'Steady' => 'bg-blue-100 text-blue-800',
                                        'Growth' => 'bg-yellow-100 text-yellow-800',
                                        'At-Risk' => 'bg-orange-100 text-orange-800',
                                        'Dormant' => 'bg-red-100 text-red-800',
                                        'Unassigned' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                <div class="group relative">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rfmColors[$wholesaler['rfm_segment']] }}">
                                        {{ $wholesaler['rfm_segment'] }}
                                    </span>
                                    @if($wholesaler['rfm_scores'])
                                        <div class="absolute bottom-full left-0 mb-2 w-48 bg-gray-800 text-white text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                            <div class="font-medium mb-1">RFM Scores:</div>
                                            <div>Recency: {{ $wholesaler['rfm_scores']['recency'] ?? 'N/A' }} days</div>
                                            <div>Frequency: {{ $wholesaler['rfm_scores']['frequency'] ?? 'N/A' }} orders</div>
                                            <div>Monetary: ${{ number_format($wholesaler['rfm_scores']['monetary'] ?? 0, 2) }}</div>
                                            <div class="mt-1 text-xs text-gray-300">
                                                Score: {{ $wholesaler['rfm_scores']['total_score'] ?? 'N/A' }}/9
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="group relative">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-light-brown text-white">
                                        {{ $wholesaler['order_size_segment'] }}
                                    </span>
                                    @if($wholesaler['order_size_scores'])
                                        <div class="absolute bottom-full left-0 mb-2 w-48 bg-gray-800 text-white text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                            <div class="font-medium mb-1">Order Details:</div>
                                            <div>Avg Quantity: {{ number_format($wholesaler['order_size_scores']['avg_quantity'] ?? 0, 2) }} kg</div>
                                            <div>Total Orders: {{ $wholesaler['order_size_scores']['total_orders'] ?? 0 }}</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($wholesaler['rfm_scores'])
                                    @php
                                        $percentage = round(($wholesaler['rfm_scores']['total_score'] / 9) * 100);
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-light-brown h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $percentage }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">No data</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $wholesaler['joined_date'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No wholesalers found</p>
                                    <p class="text-sm">Try adjusting your filters or search terms</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $wholesalerDetails->count() }} of {{ $totalWholesalers }} wholesalers
                </div>
                <div class="text-xs text-gray-500">
                    Last segmentation update: {{ $lastUpdated }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    // Listen for Livewire refresh event
    document.addEventListener('livewire:init', () => {
        Livewire.on('data-refreshed', () => {
            setTimeout(() => {
                initializeCharts();
            }, 100);
        });
    });

    function initializeCharts() {
        // RFM Color mapping
        const rfmColors = {
            'VIP': '#10B981',      // Green
            'Steady': '#3B82F6',   // Blue  
            'Growth': '#EAB308',   // Yellow
            'At-Risk': '#F97316',  // Orange
            'Dormant': '#EF4444'   // Red
        };

        // Order size colors
        const orderSizeColors = ['#854B10', '#8F6E56', '#D3BEAC']; // Brown palette
        
        // RFM Pie Chart
        const rfmData = @json($segmentStats['rfm']);
        const rfmChartElement = document.getElementById('rfm-pie-chart');
        
        if (rfmChartElement && typeof ApexCharts !== 'undefined') {
            // Clear existing chart
            rfmChartElement.innerHTML = '';
            
            const rfmOptions = {
                series: rfmData.map(item => item.count),
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent',
                },
                labels: rfmData.map(item => item.name),
                colors: rfmData.map(item => rfmColors[item.name] || '#6B7280'),
                legend: {
                    position: 'bottom',
                    fontSize: '14px',
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 500,
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 6
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    color: '#4A2924'
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    },
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif',
                        fontWeight: 600,
                        colors: ['#fff']
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val, { seriesIndex }) {
                            const segment = rfmData[seriesIndex];
                            let description = '';
                            switch(segment.name) {
                                case 'VIP': description = 'High-value customers with recent activity'; break;
                                case 'Steady': description = 'Reliable customers with consistent patterns'; break;
                                case 'Growth': description = 'Customers with growth potential'; break;
                                case 'At-Risk': description = 'Customers showing declining engagement'; break;
                                case 'Dormant': description = 'Inactive customers needing re-engagement'; break;
                            }
                            return val + ' wholesalers<br/><span style="font-size: 11px; color: #666;">' + description + '</span>';
                        }
                    }
                }
            };

            const rfmChart = new ApexCharts(rfmChartElement, rfmOptions);
            rfmChart.render();
        }

        // Order Size Bar Chart
        const orderSizeData = @json($segmentStats['order_size']);
        const orderSizeChartElement = document.getElementById('order-size-bar-chart');
        
        if (orderSizeChartElement && typeof ApexCharts !== 'undefined') {
            // Clear existing chart
            orderSizeChartElement.innerHTML = '';
            
            const orderSizeOptions = {
                series: [{
                    name: 'Wholesalers',
                    data: orderSizeData.map(item => item.count)
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent',
                },
                xaxis: {
                    categories: orderSizeData.map(item => item.name),
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                            colors: '#4A2924'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif',
                            colors: '#4A2924'
                        }
                    }
                },
                colors: orderSizeColors,
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '60%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        fontWeight: 600,
                        colors: ['#4A2924']
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val, { dataPointIndex }) {
                            const segment = orderSizeData[dataPointIndex];
                            let description = '';
                            switch(segment.name) {
                                case 'Bulk Buyers': description = 'Large volume orders (≥1,000 kg average)'; break;
                                case 'Mid-Volume': description = 'Medium volume orders (250-999 kg average)'; break;
                                case 'Micro-orders': description = 'Small volume orders (<250 kg average)'; break;
                            }
                            return val + ' wholesalers<br/><span style="font-size: 11px; color: #666;">' + description + '</span>';
                        }
                    }
                }
            };

            const orderSizeChart = new ApexCharts(orderSizeChartElement, orderSizeOptions);
            orderSizeChart.render();
        }
    }
</script>
@endpush
