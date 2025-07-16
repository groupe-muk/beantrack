<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerSegment;

class CustomerSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RFM Segments
        $rfmSegments = [
            [
                'name' => 'VIP',
                'description' => 'High-value customers with recent purchases and high frequency (RFM Score ≥8)',
                'segment_type' => 'rfm',
                'is_dynamic' => true,
            ],
            [
                'name' => 'Steady',
                'description' => 'Reliable customers with good purchase patterns (RFM Score 6-7)',
                'segment_type' => 'rfm',
                'is_dynamic' => true,
            ],
            [
                'name' => 'Growth',
                'description' => 'Customers with potential for improvement (RFM Score 5)',
                'segment_type' => 'rfm',
                'is_dynamic' => true,
            ],
            [
                'name' => 'At-Risk',
                'description' => 'Customers showing declining engagement (RFM Score 3-4)',
                'segment_type' => 'rfm',
                'is_dynamic' => true,
            ],
            [
                'name' => 'Dormant',
                'description' => 'Inactive customers requiring re-engagement (RFM Score ≤2)',
                'segment_type' => 'rfm',
                'is_dynamic' => true,
            ],
        ];

        // Order Size Segments
        $orderSizeSegments = [
            [
                'name' => 'Bulk Buyers',
                'description' => 'Large volume orders (≥1,000 kg average)',
                'segment_type' => 'order_size',
                'is_dynamic' => true,
            ],
            [
                'name' => 'Mid-Volume',
                'description' => 'Medium volume orders (250-999 kg average)',
                'segment_type' => 'order_size',
                'is_dynamic' => true,
            ],
            [
                'name' => 'Micro-orders',
                'description' => 'Small volume orders (<250 kg average)',
                'segment_type' => 'order_size',
                'is_dynamic' => true,
            ],
        ];

        // Create RFM segments
        foreach ($rfmSegments as $segment) {
            CustomerSegment::updateOrCreate(
                ['name' => $segment['name'], 'segment_type' => $segment['segment_type']],
                $segment
            );
        }

        // Create Order Size segments
        foreach ($orderSizeSegments as $segment) {
            CustomerSegment::updateOrCreate(
                ['name' => $segment['name'], 'segment_type' => $segment['segment_type']],
                $segment
            );
        }
    }
}
