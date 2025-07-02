# Recent Reports Card Component

## Usage

The `recent-reports-card` component displays the most recently generated reports in a card format similar to the progress card layout.

### Basic Usage

```blade
<x-recent-reports-card
    title="Recent Reports"
    :reports="$recentReports"
    class="min-h-[200px]" 
/>
```

### Props

- **title** (string, optional): Title for the card. Default: "Recent Reports"
- **reports** (array, required): Array of recent report data
- **class** (string, optional): Additional CSS classes for the card

### Report Data Structure

Each report in the `reports` array should have the following structure:

```php
[
    'id' => 'R00001',                           // Report ID (for view link)
    'name' => 'Monthly Supplier Demand Forecast', // Report name
    'date_generated' => Carbon::now(),           // Date/time generated
    'recipients' => 'Finance Dept, Logistics',   // Recipients (string or array)
    'status' => 'completed',                     // Report status
    'format' => 'pdf',                          // Optional: report format
]
```

### Controller Implementation

Add this method to your controller to fetch recent reports:

```php
private function getRecentReports($limit = 3): array
{
    $reports = Report::whereNotNull('last_sent')
        ->orderBy('last_sent', 'desc')
        ->limit($limit)
        ->get();

    return $reports->map(function ($report) {
        return [
            'id' => $report->id,
            'name' => $report->name,
            'date_generated' => $report->last_sent ?? $report->created_at,
            'recipients' => $report->recipients ?? 'Not specified',
            'status' => $report->status ?? 'completed',
            'format' => $report->format ?? 'pdf',
        ];
    })->toArray();
}
```

### Features

- **Responsive Design**: Adapts to different screen sizes
- **Status Indicators**: Color-coded status badges
- **Date Formatting**: Automatically formats dates nicely
- **Truncated Recipients**: Long recipient lists are truncated with ellipsis
- **View Links**: Direct links to view reports
- **Empty State**: Shows helpful message when no reports available
- **Footer Link**: Link to full reports page when reports are available

### Status Colors

The component automatically applies different colors based on report status:

- **Completed/Success**: Green
- **Failed**: Red  
- **Processing**: Orange
- **Default**: Green

### Integration

To integrate into a dashboard, add the reports data to your controller:

```php
// In your dashboard controller
private function getAdminDashboardData(): array
{
    return [
        // ... other data ...
        'recentReports' => $this->getRecentReports(3),
    ];
}
```

Then use in your view:

```blade
<x-recent-reports-card
    title="Recent Reports"
    :reports="$recentReports"
    class="min-h-[200px]" 
/>
```
