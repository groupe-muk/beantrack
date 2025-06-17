@props([

    'title' => null,
    'columnChartID',
    'chartData',          // First series data
    'chartData2',         // Second series data
    'chartCategories',
    'seriesName' => 'Value 1', // Name for the first series
    'seriesName2' => 'Value 2', // Name for the second series
    'class' => '',

])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full {{ $class }}">
   @isset($title)
        <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pb-2">{{ $title }}</h5>
    @endisset
  <div id="{{ $columnChartID }}" class="w-full"></div>

{{-- JavaScript for the Chart --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        const chartElement = document.getElementById('{{ $columnChartID }}');

        if (chartElement && typeof ApexCharts !== 'undefined') {
            const softBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-soft-brown').trim() || '#8F6E56';
            const creamBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-cream-brown').trim() || '#D3BEAC';
            const warmGray = getComputedStyle(document.documentElement).getPropertyValue('--color-warm-gray').trim() || '#332E2E';
            const offWhite = getComputedStyle(document.documentElement).getPropertyValue('--color-off-white').trim() || '#F1ECE7'; // for grid lines
            const grayText = isDark ? '#9ca3af' : '#6b7280'; // For axis labels

            const options = {
                series: [{
                    name: "{{ $seriesName }}",
                    data: @json($chartData),
                }, {
                    name: "{{ $seriesName2 }}", // Second series
                    data: @json($chartData2),   // Second series data
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '70%',
                        borderRadiusApplication: 'end',
                        borderRadius: 2,
                        // Grouped bars for multiple series per category
                        dataLabels: {
                            position: 'top',
                        },
                    },
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                // Define two distinct colors for the two series
                colors: [isDark ? softBrown : creamBrown, isDark ? creamBrown : softBrown], // Swapped colors for dark/light mode example
                grid: {
                    show: true,
                    strokeDashArray: 4,
                    padding: {
                        left: 20,
                        right: 20,
                        top: -14,
                        bottom: 0
                    },
                    borderColor: isDark ? warmGray : offWhite, // Use off-white for grid in light mode
                    xaxis: {
                        lines: {
                            show: true // Keep vertical lines for each category
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true // Horizontal grid lines
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    style: {
                        fontFamily: 'Inter, sans-serif',
                    },
                    theme: isDark ? 'dark' : 'light',
                },
                xaxis: {
                    categories: @json($chartCategories),
                    labels: {
                        style: {
                            fontFamily: 'Inter, sans-serif',
                            cssClass: 'text-xs font-normal',
                            colors: grayText // Consistent text color
                        }
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            fontFamily: 'Inter, sans-serif',
                            cssClass: 'text-xs font-normal',
                            colors: grayText // Consistent text color
                        },
                        formatter: function (value) {
                            return value;
                        }
                    },
                },
                fill: {
                    opacity: 1
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: true, // Show legend to distinguish between two series
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: {
                        colors: grayText // Legend label colors
                    }
                }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        }
    });

</script>
@endpush