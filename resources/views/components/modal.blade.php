@props([
    'id' => 'modal',
    'title' => 'Modal Title',
    'size' => 'md', // sm, md, lg, xl
    'showFooter' => true,
    'cancelText' => 'Cancel',
    'submitText' => 'Submit',
    'submitForm' => null,
    'cancelAction' => null
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl'
    ];
    $modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50']) }}>
    <div class="bg-white dark:bg-warm-gray rounded-2xl shadow-xl p-6 w-full {{ $modalSize }} mx-4">
        {{-- Modal Header --}}
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-dashboard-light dark:text-off-white">{{ $title }}</h3>
            <button 
                data-modal-close="{{ $id }}" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="modal-body">
            {{ $slot }}
        </div>

        {{-- Modal Footer (optional) --}}
        @if($showFooter)
            <div class="flex justify-end space-x-3 mt-6">
                {{ $footer ?? '' }}
                @if(empty($footer))
                    <button 
                        type="button" 
                        data-modal-cancel="{{ $id }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors duration-200">
                        {{ $cancelText }}
                    </button>
                    @if($submitForm)
                        <button 
                            type="submit" 
                            form="{{ $submitForm }}"
                            class="px-4 py-2 text-sm font-medium text-white bg-coffee-brown rounded-md hover:bg-brown transition-colors duration-200">
                            {{ $submitText }}
                        </button>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Modal JavaScript --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('{{ $id }}');
    if (!modal) return;
    
    const openBtns = document.querySelectorAll('[data-modal-open="{{ $id }}"]');
    const closeBtns = modal.querySelectorAll('[data-modal-close="{{ $id }}"]');
    const cancelBtns = modal.querySelectorAll('[data-modal-cancel="{{ $id }}"]');
    const form = modal.querySelector('form');

    // Open modal
    openBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    });

    // Open modal function
    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        // Focus on first input field
        const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    // Close modal function
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        
        // Clear form if it exists
        if (form) {
            form.reset();
            // Clear validation errors
            form.querySelectorAll('.text-red-600, .text-red-400').forEach(error => {
                error.style.display = 'none';
            });
        }
    }

    // Close modal events
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    });
    
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Auto-open modal if there are validation errors
    @if ($errors->any())
        @if(request()->routeIs('admin.users.*') || request()->routeIs('admin.users.store') || request()->routeIs('admin.users.update'))
            openModal();
        @endif
    @endif
});
</script>
@endpush
