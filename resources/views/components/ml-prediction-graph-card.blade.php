@props([
    'title',
    'chartTitle',
    'predictionChartID',
    'chartData', 
    'chartCategories',
    'description' => null,
    'class' => '',
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full {{ $class }}">
    @isset($title)
        <h5 class="text-3xl font-bold leading-none text-dashboard-light dark:text-white pb-2 pt-5">{{ $title }}</h5>
    @endisset

    <p class="text-light-brown text-sm mt-2 mb-10">Machine Learning Based Predictions for the year</p>

    @isset($chartTitle)
        <p class="text-xl text-dashboard-light font-semibold mb-5 pl-5">{{ $chartTitle }}</p>
    @endisset

    <div id="{{ $predictionChartID }}"></div>

    @if ($description)
        <p class="text-xs text-warm-gray dark:text-gray-400 mt-4">{{ $description }}</p>
    @endif


    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const chartElement = document.getElementById('{{ $predictionChartID }}');

             if (chartElement && typeof ApexCharts !== 'undefined') {
            const lightBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-light-brown').trim() || '#854B10';
            const coffeeBrown = getComputedStyle(document.documentElement).getPropertyValue('--color-coffee-brown').trim() || '#4A2924';
            const warmGray = getComputedStyle(document.documentElement).getPropertyValue('--color-warm-gray').trim() || '#332E2E';
            const offWhite = getComputedStyle(document.documentElement).getPropertyValue('--color-off-white').trim() || '#F1ECE7'; 
            const green = getComputedStyle(document.documentElement).getPropertyValue('--color-progress-bar-green').trim() || '#22C55E';
            const red = getComputedStyle(document.documentElement).getPropertyValue('--color-progress-bar-red').trim() || '#EF4444';

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
                        dashArray: [0, 7, 4, 4], 
                    },
                    colors: [
                        coffeeBrown,
                        lightBrown,
                        green,
                        red, 
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
                                show: false
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