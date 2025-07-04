<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['title'] ?? 'BeanTrack Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .report-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .summary-section {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #8B4513;
        }
        
        .summary-section h3 {
            margin-top: 0;
            color: #8B4513;
        }
        
        .data-section {
            margin-bottom: 30px;
        }
        
        .data-section h3 {
            color: #8B4513;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #8B4513;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .filters-section {
            background-color: #f0f8ff;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #4682B4;
        }
        
        .filters-section h4 {
            margin-top: 0;
            color: #4682B4;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .date-range {
            background-color: #e8f5e8;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"> BeanTrack</div>
        <div class="report-title">{{ $data['title'] ?? 'Report' }}</div>
        <div class="report-meta">
            Generated on {{ $data['generated_at']->format('F j, Y \a\t g:i A') }}
            @if($report->creator)
                | Created by {{ $report->creator->name }}
            @endif
        </div>
    </div>

    @if(isset($data['summary']))
    <div class="summary-section">
        <h3> Report Summary</h3>
        <p>{{ $data['summary'] }}</p>
    </div>
    @endif

    @if(isset($data['date_range']) && ($data['date_range']['from'] || $data['date_range']['to']))
    <div class="date-range">
        <strong> Date Range:</strong>
        @if($data['date_range']['from'] && $data['date_range']['to'])
            {{ $data['date_range']['from'] }} to {{ $data['date_range']['to'] }}
        @elseif($data['date_range']['from'])
            From {{ $data['date_range']['from'] }}
        @elseif($data['date_range']['to'])
            Up to {{ $data['date_range']['to'] }}
        @endif
    </div>
    @endif

    @if(isset($data['filters']) && !empty($data['filters']))
    <div class="filters-section">
        <h4> Applied Filters</h4>
        <ul>
            @foreach($data['filters'] as $filter => $value)
                <li><strong>{{ ucfirst(str_replace('_', ' ', $filter)) }}:</strong> {{ $value }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="data-section">
        <h3> Report Data</h3>
        
        @if(isset($data['data']) && !empty($data['data']))
            <table>
                <thead>
                    <tr>
                        @foreach(array_keys($data['data'][0]) as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['data'] as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No data available for the selected criteria.
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated by BeanTrack reporting system.</p>
        <p>For questions or support, please contact the BeanTrack team.</p>
        <p>Generated at {{ now()->format('Y-m-d H:i:s') }} UTC</p>
    </div>
</body>
</html>
