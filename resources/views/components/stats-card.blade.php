@props([
    'title',            // e.g., "Active Orders"
    'value',            // e.g., "8", "4,250 kg", "$342,850"
    'unit' => null,     // e.g., "orders", "kg", "May 25"
    'changeText' => null, // e.g., "5.2% from last period"
    'changeIconClass' => null, 
    'changeColor' => 'text-green-500', // Tailwind text color class for changeText
    'iconClass',        // Font Awesome or similar icon class, e.g., 'fa-solid fa-box'
    'iconBgClass' => 'bg-transparent', // Tailwind background color for the icon circle/square
    'iconColorClass' => 'text-soft-brown', // Tailwind text color for the icon
    'class' => '',      // Additional classes for the card wrapper
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between h-full {{ $class }}">
    {{-- Top Row: Title and Icon --}}
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-sm font-semibold text-soft-brown">{{ $title }}</h4>
        <div class="p-2 rounded-full {{ $iconBgClass }}">
            {{-- Using a placeholder for Font Awesome-like icon --}}
            <i class="fa-solid {{ $iconClass }} text-xl {{ $iconColorClass }}"></i>
        </div>
    </div>

    {{-- Middle Row: Value and Unit --}}
    <div class="flex mb-4">
        <p class="text-4xl font-bold text-coffee-brown leading-tight">{{ $value }}</p>
        @if ($unit)      
            <p class="text-3xl font-bold text-coffee-brown mt-1 ml-2">{{ $unit }}</p>
        @endif
    </div>

    {{-- Bottom Row: Change Text --}}
    <div class="flex">
    @if ($changeText)
        <i class="fa-solid {{ $changeIconClass }} text-xs {{ $changeColor}}"></i>  
        <p class="text-xs font-medium {{ $changeColor }} ml-1 mt-auto"> {{-- mt-auto pushes it to the bottom --}}
            {{ $changeText }}
        </p>
    </div>    
    @endif
</div>