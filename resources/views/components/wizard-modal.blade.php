@props([
    'id' => 'wizardModal', 
    'title' => 'Wizard', 
    'steps' => [],
    'closeFunction' => 'closeCreateReportModal',
    'saveFunction' => 'saveReportSchedule'
])

<div id="{{ $id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-dashboard-light">{{ $title }}</h3>
                <button class="text-mild-gray hover:text-warm-gray" onclick="{{ $closeFunction }}()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Wizard Steps -->
            <div class="py-4">
                <!-- Step Indicator -->
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-4">
                        @foreach($steps as $index => $step)
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 {{ $index === 0 ? 'bg-soft-brown text-white' : 'bg-soft-gray text-warm-gray' }} rounded-full flex items-center justify-center text-sm font-medium">{{ $index + 1 }}</div>
                            <div class="text-xs mt-1 text-center">{{ $step }}</div>
                        </div>
                        @if(!$loop->last)
                        <div class="w-8 h-px {{ $index === 0 ? 'bg-gray-300' : 'bg-soft-gray' }}"></div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{ $slot }}

                <!-- Modal Footer -->
                <div class="flex justify-between items-center pt-6 mt-6 border-t">
                    <button type="button" id="prev-btn" class="px-4 py-2 text-sm font-medium text-mild-gray bg-light-background rounded-md hover:bg-gray-300 hidden" onclick="previousStep()">
                        Previous
                    </button>
                    <div class="flex space-x-2">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-mild-gray bg-light-background rounded-md hover:bg-gray-300" onclick="{{ $closeFunction }}()">
                            Cancel
                        </button>
                        <button type="button" id="next-btn" class="px-4 py-2 bg-light-brown text-white rounded-md hover:bg-brown" onclick="nextStep()">
                            Next
                        </button>
                        <button type="button" id="save-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 hidden" onclick="{{ $saveFunction }}()">
                            Save Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
