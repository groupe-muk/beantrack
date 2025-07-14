@props([
    'title' => 'Recent Reports', // Default title for the card
    'reports' => [],             // Array of recent reports
    'class' => '',               // Optional additional CSS classes for the card
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col {{ $class }}">
    <h5 class="text-xl font-bold leading-none text-dashboard-light dark:text-white pt-5 pb-10">
        {{ $title }}
    </h5>

    <div class="space-y-4">
        @forelse($reports as $report)
            @php
                // Formatting the date nicely
                $dateGenerated = isset($report['date_generated']) ? 
                    \Carbon\Carbon::parse($report['date_generated'])->format('M j, Y \a\t g:i A') : 
                    'Unknown';
                
                // Handle recipients - could be string or array
                $recipients = $report['recipients'] ?? 'No recipients';
                if (is_array($recipients)) {
                    $recipients = implode(', ', $recipients);
                }
                
                // Truncate long recipient lists
                if (strlen($recipients) > 40) {
                    $recipients = substr($recipients, 0, 37) . '...';
                }
                
                // Determine status color
                $statusColorClass = 'bg-status-background-green text-status-text-green';
                switch (strtolower($report['status'] ?? 'completed')) {
                    case 'failed':
                        $statusColorClass = 'bg-status-background-red text-status-text-red';
                        break;
                    case 'processing':
                        $statusColorClass = 'bg-status-background-orange text-status-text-orange';
                        break;
                    case 'completed':
                    case 'success':
                    default:
                        $statusColorClass = 'bg-status-background-green text-status-text-green';
                        break;
                }
            @endphp

            <div class="flex flex-col gap-3 bg-pale-brown/50 p-4 rounded-2xl">
                {{-- Report Name and Status --}}
                <div class="flex justify-between items-start text-sm font-medium text-dashboard-light dark:text-white">
                    <span class="font-semibold truncate flex-1 mr-2">{{ $report['name'] ?? 'Untitled Report' }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColorClass }} whitespace-nowrap">
                        {{ ucfirst($report['status'] ?? 'Completed') }}
                    </span>
                </div>

                {{-- Date Generated --}}
                <div class="text-xs text-soft-brown dark:text-gray-400">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Generated: <span class="font-medium text-dashboard-light">{{ $dateGenerated }}</span>
                </div>

                {{-- Recipients --}}
                <div class="text-xs text-soft-brown dark:text-gray-400">
                    <i class="fas fa-users mr-1"></i>
                    Recipients: <span class="font-medium text-dashboard-light">{{ $recipients }}</span>
                </div>

                {{-- Action Button --}}
                <div class="flex justify-end pt-2">
                    @if(isset($report['id']))
                        @php
                            $currentUser = Auth::user();
                            $viewRoute = 'reports.view'; // Default for admin
                            
                            if ($currentUser->role === 'vendor') {
                                $viewRoute = 'reports.vendor.view';
                            } elseif ($currentUser->role === 'supplier') {
                                $viewRoute = 'reports.supplier.view';
                            }
                        @endphp
                        
                        <a href="{{ route($viewRoute, $report['id']) }}" 
                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-light-brown hover:bg-brown rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown"
                           target="_blank">
                            <i class="fas fa-eye mr-1"></i>
                            View Report
                        </a>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                            <i class="fas fa-eye mr-1"></i>
                            Unavailable
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-file-alt text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">No recent reports available.</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Generated reports will appear here.</p>
            </div>
        @endforelse
    </div>

    {{-- Footer with link to full reports page --}}
    @if(count($reports) > 0)
        <div class="mt-6 pt-4 border-t border-gray-200">
            @php
                $currentUser = Auth::user();
                $reportsRoute = 'reports.index'; // Default for admin
                
                if ($currentUser->role === 'vendor') {
                    $reportsRoute = 'reports.vendor';
                } elseif ($currentUser->role === 'supplier') {
                    $reportsRoute = 'reports.supplier';
                }
            @endphp
            
            <a href="{{ route($reportsRoute) }}" 
               class="text-xs text-light-brown hover:text-brown font-medium flex items-center justify-center transition-colors duration-200">
                View All Reports
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    @endif
</div>
