@props([
    'title',
    'lineChartID',
    'chartData', 
    'chartCategories',
    'class' => '',
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full {{ $class }}">
    @isset($title)
        <h5 class="text-3xl font-bold leading-none text-dashboard-light dark:text-white pb-2 pt-5">{{ $title }}</h5>
    @endisset

    <div id="{{ $lineChartID }}" class="pt-2"></div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const chartElement = document.getElementById('{{ $lineChartID }}');

             if (chartElement && typeof ApexCharts !== 'undefined') {
            const lightBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-light-brown').trim() || '#854B10';
            const coffeeBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-coffee-brown').trim() || '#4A2924';
            const warmGray = getComputedStyle(document.documentElement).getPropertyValue('--color-warm-gray').trim() || '#332E2E';
            const offWhite = getComputedStyle(document.documentElement).getPropertyValue('--color-off-white').trim() || '#F1ECE7'; 
            const softBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-soft-brown').trim() || '#8F6E56';
            

                const options = {
                    series: @json($chartData),
                    chart: {
                        type: 'line',
                        height: 350, 
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
                    stroke: {
                        curve: 'smooth', 
                        width: 3, 
                    },
                    colors: [
                        coffeeBrown,
                        lightBrown,
                        softBrown, 
                    ],
                    grid: {
                        show: true,
                        strokeDashArray: 4,
                        padding: {
                            left: 20,
                            right: 20,
                            top: -14,
                            bottom: 0
                        },
                        borderColor: isDark ? warmGray : offWhite, 
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        style: {
                            fontFamily: 'Inter, sans-serif',
                        },
                        x: {
                            format: 'MMM yyyy' 
                        }
                    },
                    xaxis: {
                        categories: @json($chartCategories),
                        labels: {
                            style: {
                                fontFamily: 'Inter, sans-serif',
                                cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
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
                        tickAmount: 5,
                        labels: {
                            style: {
                                fontFamily: 'Inter, sans-serif',
                                cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                            },
                            formatter: function (value) {
                                return value; 
                            }
                        },
                    },
                    legend: {
                        show: true, 
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif',
                        labels: {
                            colors: isDark ? warmGray : offWhite, // Legend text color
                        },
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 12,
                        },
                        itemMargin: {
                            horizontal: 10,
                            vertical: 0
                        },
                    }
                };

              const chart = new ApexCharts(chartElement, options);
                chart.render();
            }
        });
    </script>
    @endpush
</div>