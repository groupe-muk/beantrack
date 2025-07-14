@props([
    'title' => 'Untitled',     // e.g., "Active Orders" - now has default
    'value' => '0',            // e.g., "8", "4,250 kg", "$342,850" - now has default
    'unit' => null,            // e.g., "orders", "kg", "May 25"
    'changeText' => null,      // e.g., "5.2% from last period"
    'changeIconClass' => null, 
    'changeColor' => null,     // Can be 'green', 'red', or null for default grey
    'changeType' => null,      // Alternative: 'positive', 'negative', or null
    'iconClass' => 'fa-chart-bar', // Font Awesome or similar icon class - now has default
    'iconBgClass' => 'bg-transparent', // Tailwind background color for the icon circle/square
    'iconColorClass' => 'text-soft-brown', // Tailwind text color for the icon
    'class' => '',             // Additional classes for the card wrapper
    'id' => null,              // Optional ID for dynamic updates
])

@php
    // Determine the change color class based on changeColor or changeType
    $changeColorClass = 'text-gray-500'; // Default grey
    
    if ($changeColor === 'green' || $changeType === 'positive') {
        $changeColorClass = 'text-green-500';
    } elseif ($changeColor === 'red' || $changeType === 'negative') {
        $changeColorClass = 'text-red-500';
    } elseif ($changeColor && str_contains($changeColor, 'text-')) {
       
        $changeColorClass = $changeColor;
    }
@endphp

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full {{ $class }}"@if($id) id="{{ $id }}"@endif>
    {{-- Top Row: Title and Icon --}}
    <div class="flex justify-between items-center mb-4">
        <h4 class="font-semibold text-soft-brown">{{ $title }}</h4>
        <div class="p-2 rounded-full {{ $iconBgClass }}">
            {{-- Using a placeholder for Font Awesome-like icon --}}
            <i class="fa-solid {{ $iconClass }} text-xl {{ $iconColorClass }}"></i>
        </div>
    </div>

    {{-- Middle Row: Value and Unit --}}
    <div class="flex mb-4 flex-wrap">
    <p class="text-4xl font-bold text-coffee-brown leading-tight" data-value="{{ $value }}">
        <span
            @if($title === 'Out Of Stock')
                id="out-of-stock-value"
            @elseif($title === 'Low Stock Alerts')
                id="low-stock-value"
            @elseif($title === 'Total Quantity')
                id="total-quantity"
            @endif
        >{{ $value }}</span>
        @if ($unit)      
            <span class="text-3xl font-bold text-coffee-brown ml-1">{{ $unit }}</span>
        @endif
    </p>
    </div>

    {{-- Bottom Row: Change Text --}}
    
    @if ($changeText)
    <div class="flex">
        <i class="fa-solid {{ $changeIconClass }} text-xs {{ $changeColorClass }}"></i>  
        <p class="text-xs font-medium {{ $changeColorClass }} ml-1 mt-auto"> {{-- mt-auto pushes it to the bottom --}}
            {{ $changeText }}
        </p>
    </div>    
    @endif

</div>    