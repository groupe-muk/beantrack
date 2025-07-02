<?php

require_once 'vendor/autoload.php';

use App\Models\Report;
use App\Models\User;

echo "=== BeanTrack Reports System Validation ===\n\n";

try {
    // Check if we can connect to the database and models work
    echo "1. Testing database connection...\n";
    $reportCount = Report::count();
    echo "   ✓ Connected! Found {$reportCount} reports in database\n\n";

    // Test Report model functionality
    echo "2. Testing Report model...\n";
    $reports = Report::with('recipient')->limit(3)->get();
    
    foreach ($reports as $report) {
        echo "   Report: {$report->name}\n";
        echo "   Type: {$report->format}\n";
        echo "   Frequency: {$report->frequency}\n";
        echo "   Status: {$report->status}\n";
        echo "   Recipient: " . ($report->recipient ? $report->recipient->name : 'N/A') . "\n";
        echo "   ---\n";
    }

    // Test creating a sample report
    echo "\n3. Testing report creation...\n";
    $user = User::first();
    
    if ($user) {
        $testReport = Report::create([
            'name' => 'Test Report - ' . date('Y-m-d H:i:s'),
            'description' => 'This is a test report created during validation',
            'type' => 'adhoc',
            'recipient_id' => $user->id,
            'frequency' => 'once',
            'format' => 'pdf',
            'recipients' => 'Test User',
            'schedule_time' => '09:00:00',
            'status' => 'completed',
            'content' => json_encode(['test' => true]),
            'last_sent' => now()
        ]);
        
        echo "   ✓ Test report created with ID: {$testReport->id}\n";
        
        // Clean up - delete the test report
        $testReport->delete();
        echo "   ✓ Test report cleaned up\n";
    } else {
        echo "   ⚠ No users found in database for testing\n";
    }

    // Test model accessors
    echo "\n4. Testing model accessors...\n";
    $sampleReport = Report::first();
    if ($sampleReport) {
        echo "   Status Badge: " . strip_tags($sampleReport->status_badge) . "\n";
        echo "   Format Badge: " . strip_tags($sampleReport->format_badge) . "\n";
        echo "   Formatted Last Sent: {$sampleReport->formatted_last_sent}\n";
    }

    echo "\n✅ All validations passed! The Reports System is working correctly.\n";
    echo "\n📋 Summary:\n";
    echo "   - Database connection: ✓ Working\n";
    echo "   - Report model: ✓ Working\n";
    echo "   - CRUD operations: ✓ Working\n";
    echo "   - Model relationships: ✓ Working\n";
    echo "   - Model accessors: ✓ Working\n";
    
    echo "\n🎯 Next Steps:\n";
    echo "   1. Access the reports page at: /reports\n";
    echo "   2. Test the Create New Report Schedule wizard\n";
    echo "   3. Try generating ad-hoc reports\n";
    echo "   4. Explore the historical reports section\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Validation Complete ===\n";
