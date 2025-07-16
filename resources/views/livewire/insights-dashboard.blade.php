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
