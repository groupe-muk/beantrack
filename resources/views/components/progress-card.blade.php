@props([
    'title' => null, // Optional title for the card
    'items' => [],   // Array of items, each with name, available, allocated, and statusLabel
    'class' => '',   // Optional additional CSS classes for the card
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col {{ $class }}">
    @isset($title)
        <h5 class="text-xl font-bold leading-none text-dashboard-light dark:text-white pt-5 pb-10">
            {{ $title }}
        </h5>
    @endisset

    <div class="space-y-4">
        @forelse($items as $item)
            @php
                $available = (float)($item['available'] ?? 0);
                $allocated = (float)($item['allocated'] ?? 0);
                $percentage = ($available > 0) ? round(($available / $allocated) * 100) : 0;

                // Determine progress bar color and status label styling
                $progressBarColorClass = 'bg-gray-300'; // Default gray
                $statusLabelTextClass = 'text-gray-700'; // Default gray text
                $statusLabelBgClass = 'bg-gray-100';    // Default gray background for label

                switch ($item['statusLabel'] ?? '') {
                    case 'Healthy':
                        $progressBarColorClass = 'bg-progress-bar-green'; 
                        $statusLabelTextClass = 'text-status-text-green';     
                        $statusLabelBgClass = 'bg-status-background-green'; 
                        break;
                    case 'Low':
                        $progressBarColorClass = 'bg-progress-bar-orange'; 
                        $statusLabelTextClass = 'text-status-text-orange';
                        $statusLabelBgClass = 'bg-status-background-orange';
                        break;
                    case 'Critical':
                        $progressBarColorClass = 'bg-progress-bar-red';   
                        $statusLabelTextClass = 'text-status-text-red';
                        $statusLabelBgClass = 'bg-status-background-red';
                        break;
                    default:
                        // Default styling for other statuses or if not specified
                        $progressBarColorClass = 'bg-status-background-gray'; 
                        $statusLabelTextClass = 'text-status-text-gray';
                        $statusLabelBgClass = 'bg-status-background-gray';
                        break;
                }
            @endphp

            <div class="flex flex-col gap-2 bg-pale-brown/50 p-6 pl-4 pr-4 rounded-2xl">
                {{-- Item Name and Status Label --}}
                <div class="flex justify-between items-center text-sm font-medium text-dashboard-light dark:text-white pb-3">
                    <span>{{ $item['name'] ?? 'N/A' }}</span>
                    @if(isset($item['statusLabel']))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusLabelBgClass }} {{ $statusLabelTextClass }}">
                            {{ $item['statusLabel'] }}
                        </span>
                    @endif
                </div>

                {{-- Available and Allocated Quantities --}}
                <div class="flex justify-between items-center text-xs text-soft-brown dark:text-gray-400 pb-3">
                    <span>Available: <span class="font-bold text-dashboard-light">{{ $item['available'] ?? 'N/A' }} kg</span></span>
                    <span>Allocated Space: <span class="font-bold text-dashboard-light">{{ $item['allocated'] ?? 'N/A' }} kg</span></span>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-soft-gray rounded-full h-2.5 dark:bg-warm-gray">
                    <div class="h-2.5 rounded-full {{ $progressBarColorClass }}" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 dark:text-gray-400">No inventory items to display.</p>
        @endforelse
    </div>
</div>