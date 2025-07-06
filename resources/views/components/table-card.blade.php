@props([
    'title' => null,           // Optional title for the card
    'headers' => [],           // Array of table header strings
    'data' => [],              // Array of arrays/objects for table rows
    'emptyMessage' => 'No data available.', // Message when data is empty
    'class' => '',             // Optional additional CSS classes for the card
])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col {{ $class }}">
    @isset($title)
        <h5 class="text-xl font-bold leading-none text-coffee-brown dark:text-white pb-4 mb-5 mt-5">
            {{ $title }}
        </h5>
    @endisset

    <div class="relative overflow-x-auto rounded-lg">
        @if (count($data) > 0)
            <table class="w-full text-sm text-left text-dashboard-light dark:text-gray-400">
                <thead class="text-xs text-soft-brown uppercase bg-transparent border-b border-t-0 dark:text-gray-200">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="px-6 py-3 rounded-t-lg">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
               <tbody>
                    @foreach ($data as $rowIndex => $row)
                        <tr class="bg-transparent border-b border-soft-gray last:border-b-0 dark:bg-dark-background dark:border-warm-gray hover:bg-light-gray dark:hover:bg-coffee-brown transition duration-150 ease-in-out group">
                            @foreach ($headers as $header)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($header === 'Status')
                                        @php
                                            $status = $row[$header] ?? '';
                                            $pillClasses = ''; 

                                            switch ($status) {
                                                case 'In Stock':
                                                case 'Passed':
                                                case 'Active':
                                                case 'Delivered': 
                                                    $pillClasses = 'bg-status-background-green text-status-text-green';
                                                    break;
                                                case 'Out of Stock':
                                                case 'Rejected':
                                                case 'Inactive': 
                                                    $pillClasses = 'bg-status-background-red text-status-text-red';
                                                    break;
                                                case 'Limited Stock':
                                                case 'Pending':
                                                    $pillClasses = 'bg-status-background-orange text-status-text-orange';
                                                    break;
                                                case 'Confirmed':
                                                    $pillClasses = 'bg-status-background-blue text-status-text-blue';
                                                    break;
                                                case 'Shipped':
                                                    $pillClasses = 'bg-status-background-purple text-status-text-purple';
                                                    break;        
                                                default:
                                                    $pillClasses = 'bg-status-background-gray text-status-text-gray'; 
                                                    break;
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pillClasses }}">
                                            {{ $status }}
                                        </span>
                                    @else
                                        {{ $row[$header] ?? '' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-6 text-center text-coffee-brown dark:text-white">
                {{ $emptyMessage }}
            </div>
        @endif
    </div>
</div>