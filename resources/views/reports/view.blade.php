@extends('layouts.main-view')

@push('styles')
<style>
    .report-container {
        max-width: 1200px;
        margin: 0 auto;
        background: var(--color-white);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .report-header {
        padding: 2rem;
        background: linear-gradient(135deg, var(--color-light-brown) 0%, var(--color-brown) 100%);
        border-radius: 8px 8px 0 0;
    }
    
    .report-title {
        font-size: 2rem;
        font-weight: bold;
        color: var(--color-white);
        margin-bottom: 0.5rem;
    }
    
    .report-meta {
        color: var(--color-white);
        font-size: 0.875rem;
    }
    
    .report-content {
        padding: 2rem;
    }
    
    .summary-section {
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .summary-item {
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    
    .summary-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .summary-value {
        font-size: 1.25rem;
        font-weight: bold;
        color: #1f2937;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.875rem;
    }
    
    .data-table th {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
    }
    
    .data-table td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        color: #1f2937;
    }
    
    .data-table tr:nth-child(even) {
        background: #f9fafb;
    }
    
    .data-table tr:hover {
        background: #f3f4f6;
    }
    
    .report-actions {
        position: sticky;
        top: 20px;
        float: right;
        margin-left: 2rem;
    }
    
    .action-button {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        margin: 0.25rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        color: var(--color-brown);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .action-button:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        text-decoration: none;
    }
    
    .action-button i {
        margin-right: 0.5rem;
    }
    
    .print-only {
        display: none;
    }
    
    @media print {
        .report-actions,
        .no-print {
            display: none !important;
        }
        
        .print-only {
            display: block;
        }
        
        .report-container {
            box-shadow: none;
            border: none;
            
        }
        
        .report-header {
            background: white;
            border-bottom: 2px solid #000;
            
        }
    }
</style>
@endpush

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Back Navigation -->
    <div class="mb-6 no-print">
        @php
            $backRoute = 'reports.index'; // Default for admin
            if (Auth::user()->role === 'supplier') {
                $backRoute = 'reports.supplier';
            } elseif (Auth::user()->role === 'vendor') {
                $backRoute = 'reports.vendor';
            }
        @endphp
        <a href="{{ route($backRoute) }}" class="inline-flex items-center text-sm text-white bg-light-brown p-2 rounded">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Reports
        </a>
    </div>

    <!-- Report Actions -->
    <div class="report-actions no-print pt-2 pr-2">
        <a href="{{ route('reports.download', $report->id) }}" class="action-button">
            <i class="fas fa-download"></i>
            Download
        </a>
        <button onclick="window.print()" class="action-button">
            <i class="fas fa-print"></i>
            Print
        </button>
        <button onclick="sharePage()" class="action-button">
            <i class="fas fa-share"></i>
            Share
        </button>
    </div>

    <!-- Report Container -->
    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1 class="report-title">{{ $reportData['title'] }}</h1>
            <div class="report-meta">
                <p><strong>Period:</strong> {{ $reportData['period'] }}</p>
                <p><strong>Generated:</strong> {{ $generatedAt }}</p>
                <p><strong>Report ID:</strong> {{ $report->id }}</p>
                <div class="print-only">
                    <p><strong>Generated by:</strong> BeanTrack Reporting System</p>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <!-- Summary Section -->
            <h3 class="text-lg font-semibold text-dashboard-light mb-4">Summary</h3>
            <div class="summary-section">    
                <div class="summary-grid">
                    @foreach($reportData['summary'] as $key => $value)
                    <div class="summary-item">
                        <div class="summary-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                        <div class="summary-value">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Detailed Data Section -->
            <div>
                <h3 class="text-lg font-semibold text-dashboard-light mb-4">Detailed Data</h3>
                
                @if(count($reportData['data']) > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                @foreach($reportData['data'][0] as $header)
                                <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($reportData['data'], 1) as $row)
                            <tr>
                                @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-table text-3xl mb-4"></i>
                    <p>No data available for this report.</p>
                </div>
                @endif
            </div>

            <!-- Report Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-sm text-gray-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p><b>Report Format:</b> {{ strtoupper($report->format) }}</p>
                        <p><b>Report Type:</b> {{ ucfirst($report->type) }}</p>
                    </div>
                    <div class="text-right">
                        <p>Generated by BeanTrack</p>
                        <p>{{ now()->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sharePage() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $reportData["title"] }}',
            text: 'Report: {{ $reportData["title"] }} - {{ $reportData["period"] }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Report URL copied to clipboard!');
        });
    }
}

// Auto-focus for better accessibility
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to content
    if (window.location.hash) {
        document.querySelector(window.location.hash)?.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
@endsection
