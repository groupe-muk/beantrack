<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'id' => 'U00001',
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function it_can_display_reports_dashboard()
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/reports');
        
        $response->assertStatus(200);
        $response->assertViewIs('reports.report');
        $response->assertViewHas(['activeReports', 'generatedToday', 'pendingReports', 'successRate']);
    }

    /** @test */
    public function it_can_create_a_report_schedule()
    {
        $this->actingAs($this->user);
        
        $reportData = [
            'template' => 'Monthly Supplier Demand Forecast',
            'recipients' => ['Finance Dept', 'Logistics Team'],
            'frequency' => 'monthly',
            'format' => 'pdf',
            'schedule_time' => '09:00',
            'schedule_day' => 'monday'
        ];
        
        $response = $this->post('/reports', $reportData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('reports', [
            'name' => 'Monthly Supplier Demand Forecast',
            'frequency' => 'monthly',
            'format' => 'pdf'
        ]);
    }

    /** @test */
    public function it_can_generate_adhoc_report()
    {
        $this->actingAs($this->user);
        
        $adhocData = [
            'report_type' => 'sales_data',
            'from_date' => '2025-06-01',
            'to_date' => '2025-06-26',
            'format' => 'excel',
            'filters' => []
        ];
        
        $response = $this->post('/reports/adhoc', $adhocData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_get_report_library_data()
    {
        $this->actingAs($this->user);
        
        // Create some test reports
        Report::factory()->count(3)->create([
            'recipient_id' => $this->user->id
        ]);
        
        $response = $this->get('/reports/library');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'type',
                    'frequency',
                    'recipients',
                    'last_generated',
                    'status',
                    'actions'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_delete_a_report()
    {
        $this->actingAs($this->user);
        
        $report = Report::factory()->create([
            'recipient_id' => $this->user->id,
            'name' => 'Test Report'
        ]);
        
        $response = $this->delete("/reports/{$report->id}");
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('reports', [
            'id' => $report->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_report()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/reports', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['template', 'recipients', 'frequency', 'format']);
    }

    /** @test */
    public function it_can_generate_report_now()
    {
        $this->actingAs($this->user);
        
        $report = Report::factory()->create([
            'recipient_id' => $this->user->id,
            'name' => 'Test Report',
            'status' => 'active'
        ]);
        
        $response = $this->post("/reports/{$report->id}/generate");
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Check that last_sent was updated
        $report->refresh();
        $this->assertNotNull($report->last_sent);
    }
}
