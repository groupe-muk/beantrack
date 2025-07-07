@props([
    'title',
    'chartTitle',
    'predictionChartID',
    'chartData', 
    'chartCategories',
    'description' => null,
    'class' => '',
])

<div class="bg-white p-6 w-full rounded-2xl shadow-lg border border-gray-100 flex flex-col justify-between h-full {{ $class }}">
    @isset($title)
        <div class="border-b border-gray-100 pb-4 mb-6">
            <h5 class="text-3xl font-bold leading-none text-gray-800 dark:text-white">{{ $title }}</h5>
            <p class="text-gray-600 text-sm mt-2">AI-powered price forecasting with historical data analysis</p>
        </div>
    @endisset

    @isset($chartTitle)
        <div class="mb-6">
            <p class="text-xl text-gray-800 font-semibold mb-2">{{ $chartTitle }}</p>
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-0.5 bg-blue-600 rounded"></div>
                    <span class="text-gray-600 font-medium">Historical Data</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-0.5 bg-purple-600 rounded border-t-2 border-dashed border-purple-600"></div>
                    <span class="text-gray-600 font-medium">ML Predictions</span>
                </div>
            </div>
        </div>
    @endisset

    <div id="{{ $predictionChartID }}" class="flex-1"></div>

    @if ($description)
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600 leading-relaxed">{{ $description }}</p>
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const chartElement = document.getElementById('{{ $predictionChartID }}');

            if (chartElement && typeof ApexCharts !== 'undefined') {
                // Professional color palette
                const actualColor = '#2563EB';      // Blue for actual data
                const predictedColor = '#7C3AED';   // Purple for predictions
                const gridColor = isDark ? '#374151' : '#F3F4F6';
                const textColor = isDark ? '#D1D5DB' : '#6B7280';
                
                // Find the transition point between actual and predicted data
                const chartData = @json($chartData);
                let transitionIndex = -1;
                
                if (chartData.length >= 2) {
                    const actualData = chartData[0].data || [];
                    const predictedData = chartData[1].data || [];
                    for (let i = 0; i < actualData.length; i++) {
                        if (actualData[i] === null) {
                            transitionIndex = i;
                            break;
                        }
                    }

                    // Ensure the predicted line connects seamlessly from the transition point
                    if (transitionIndex > 0) {
                        // Duplicate the last actual value into the predicted series one step earlier
                        predictedData[transitionIndex - 1] = actualData[transitionIndex - 1];

                        // If the first real predicted point is null, set it equal to that duplicated value as well (smooth start)
                        if (predictedData[transitionIndex] === null) {
                            predictedData[transitionIndex] = actualData[transitionIndex - 1];
                        }
                    }
                }

                const options = {
                    series: chartData,
                    chart: {
                        type: 'line',
                        height: 400,
                        fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                        toolbar: {
                            show: false
                        },
                        background: 'transparent',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1200,
                            animateGradually: {
                                enabled: true,
                                delay: 100
                            }
                        },
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 1,
                            left: 1,
                            blur: 3,
                            opacity: 0.1
                        }
                    },
                    colors: [actualColor, predictedColor],
                    fill: {
                        type: 'solid',
                        opacity: [1, 1] // Ensure lines are fully visible with no area fill
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [4, 4], // Thicker lines for clear visibility
                        dashArray: [0, 8]
                    },
                    markers: {
                        size: 0, // Hide point markers to emphasize the connecting lines
                        strokeColors: 'transparent',
                        hover: {
                            size: 6 // Show a small marker on hover for better UX
                        }
                    },
                    grid: {
                        show: true,
                        strokeDashArray: 3,
                        borderColor: gridColor,
                        position: 'back',
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 20,
                            bottom: 0,
                            left: 20
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        style: {
                            fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                            fontSize: '14px'
                        },
                        x: {
                            format: 'dd MMM yyyy'
                        },
                        y: {
                            formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                                if (value === null) return '';
                                
                                const seriesName = w.globals.seriesNames[seriesIndex];
                                const prefix = seriesName === 'Actual' ? 'Historical: ' : 'Predicted: ';
                                return prefix + '$' + value.toFixed(2) + '/lb';
                            }
                        },
                        marker: {
                            show: true
                        },
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
                            const actualValue = series[0][dataPointIndex];
                            const predictedValue = series[1][dataPointIndex];
                            const date = w.globals.categoryLabels[dataPointIndex];
                            
                            let content = `<div class="px-3 py-2">
                                <div class="font-semibold text-gray-800 mb-2">${date}</div>`;
                            
                            if (actualValue !== null) {
                                content += `<div class="flex items-center mb-1">
                                    <div class="w-3 h-3 rounded-full bg-blue-600 mr-2"></div>
                                    <span class="text-sm">Historical: <strong>$${actualValue.toFixed(2)}/lb</strong></span>
                                </div>`;
                            }
                            
                            if (predictedValue !== null) {
                                content += `<div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-purple-600 mr-2"></div>
                                    <span class="text-sm">ML Forecast: <strong>$${predictedValue.toFixed(2)}/lb</strong></span>
                                </div>`;
                            }
                            
                            content += `</div>`;
                            return content;
                        }
                    },
                    xaxis: {
                        categories: @json($chartCategories),
                        labels: {
                            style: {
                                fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                                fontSize: '12px',
                                fontWeight: 500,
                                colors: textColor
                            },
                            rotate: -45,
                            rotateAlways: false
                        },
                        axisBorder: {
                            show: true,
                            color: gridColor,
                            height: 1
                        },
                        axisTicks: {
                            show: true,
                            color: gridColor,
                            height: 6
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Price ($/lb)',
                            style: {
                                fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                                fontSize: '14px',
                                fontWeight: 600,
                                color: textColor
                            }
                        },
                        labels: {
                            style: {
                                fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                                fontSize: '12px',
                                fontWeight: 500,
                                colors: textColor
                            },
                            formatter: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        },
                        axisBorder: {
                            show: true,
                            color: gridColor
                        },
                        axisTicks: {
                            show: true,
                            color: gridColor
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: false,
                        fontSize: '14px',
                        fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                        fontWeight: 500,
                        labels: {
                            colors: textColor,
                            useSeriesColors: false
                        },
                        markers: {
                            width: 16,
                            height: 4,
                            strokeWidth: 0,
                            strokeColor: '#fff',
                            radius: 2,
                            customHTML: function() {
                                return [
                                    '<div style="background: ' + actualColor + '; width: 16px; height: 4px; border-radius: 2px;"></div>',
                                    '<div style="background: ' + predictedColor + '; width: 16px; height: 4px; border-radius: 2px; border-top: 2px dashed ' + predictedColor + '; background-clip: padding-box;"></div>'
                                ];
                            }
                        },
                        itemMargin: {
                            horizontal: 20,
                            vertical: 5
                        }
                    },
                    // Add vertical line annotation to separate actual from predicted data
                    annotations: transitionIndex > 0 ? {
                        xaxis: [{
                            x: @json($chartCategories)[transitionIndex],
                            strokeDashArray: 4,
                            borderColor: '#9CA3AF',
                            opacity: 0.8,
                            label: {
                                borderColor: '#9CA3AF',
                                style: {
                                    color: '#fff',
                                    background: '#6B7280',
                                    fontSize: '12px',
                                    fontWeight: 500,
                                    fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif'
                                },
                                text: 'Forecast Start',
                                position: 'top',
                                offsetY: 0
                            }
                        }]
                    } : {},
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 320
                            },
                            legend: {
                                position: 'bottom',
                                horizontalAlign: 'center'
                            },
                            xaxis: {
                                labels: {
                                    rotate: -45
                                }
                            }
                        }
                    }]
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();

                // Add resize handler for responsiveness
                window.addEventListener('resize', function() {
                    chart.resize();
                });
            }
        });
    </script>
    @endpush
</div>