<?php

namespace App\Services;

use App\Models\Wholesaler;
use App\Models\CustomerSegment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WholesalerSegmentationService
{
    private $period;
    
    public function __construct()
    {
        $this->period = now()->subDays(90);
    }

    /**
     * Generate segments for all wholesalers
     */
    public function generateSegments()
    {
        $wholesalers = Wholesaler::with(['orders' => function($query) {
            $query->where('order_date', '>=', $this->period)
                  ->whereNotNull('order_date');
        }])->get();

        foreach ($wholesalers as $wholesaler) {
            $this->assignRfmSegment($wholesaler);
            $this->assignOrderSizeSegment($wholesaler);
        }
    }

    /**
     * Calculate and assign RFM segment for a wholesaler
     */
    private function assignRfmSegment(Wholesaler $wholesaler)
    {
        $orders = $wholesaler->orders;
        
        // Calculate RFM scores
        $recency = $this->calculateRecency($orders);
        $frequency = $this->calculateFrequency($orders);
        $monetary = $this->calculateMonetary($orders);
        
        // Convert to RFM scores (1-3 scale)
        $rScore = $this->getRecencyScore($recency);
        $fScore = $this->getFrequencyScore($frequency);
        $mScore = $this->getMonetaryScore($monetary);
        
        $totalScore = $rScore + $fScore + $mScore;
        
        // Determine segment based on total score
        $segmentName = $this->getRfmSegmentName($totalScore);
        
        // Find the segment
        $segment = CustomerSegment::where('name', $segmentName)
                                 ->where('segment_type', 'rfm')
                                 ->first();
        
        if ($segment) {
            // Remove existing RFM segment assignments
            $rfmSegmentIds = CustomerSegment::rfm()->pluck('id')->toArray();
            $wholesaler->segments()->wherePivotIn('segment_id', $rfmSegmentIds)->detach();
            
            // Assign new segment
            $wholesaler->segments()->attach($segment->id, [
                'scores' => json_encode([
                    'recency' => $recency,
                    'frequency' => $frequency,
                    'monetary' => $monetary,
                    'r_score' => $rScore,
                    'f_score' => $fScore,
                    'm_score' => $mScore,
                    'total_score' => $totalScore
                ])
            ]);
        }
    }

    /**
     * Calculate and assign order size segment for a wholesaler
     */
    private function assignOrderSizeSegment(Wholesaler $wholesaler)
    {
        $orders = $wholesaler->orders;
        
        if ($orders->isEmpty()) {
            $avgQuantity = 0;
        } else {
            $avgQuantity = $orders->avg('quantity') ?? 0;
        }
        
        // Determine segment based on average quantity
        $segmentName = $this->getOrderSizeSegmentName($avgQuantity);
        
        // Find the segment
        $segment = CustomerSegment::where('name', $segmentName)
                                 ->where('segment_type', 'order_size')
                                 ->first();
        
        if ($segment) {
            // Remove existing order size segment assignments
            $orderSizeSegmentIds = CustomerSegment::orderSize()->pluck('id')->toArray();
            $wholesaler->segments()->wherePivotIn('segment_id', $orderSizeSegmentIds)->detach();
            
            // Assign new segment
            $wholesaler->segments()->attach($segment->id, [
                'scores' => json_encode([
                    'avg_quantity' => $avgQuantity,
                    'total_orders' => $orders->count()
                ])
            ]);
        }
    }

    /**
     * Calculate recency (days since last order)
     */
    private function calculateRecency($orders)
    {
        if ($orders->isEmpty()) {
            return 999; // Very high recency for no orders
        }
        
        $lastOrderDate = $orders->max('order_date');
        return $lastOrderDate ? Carbon::parse($lastOrderDate)->diffInDays(now()) : 999;
    }

    /**
     * Calculate frequency (number of orders in period)
     */
    private function calculateFrequency($orders)
    {
        return $orders->count();
    }

    /**
     * Calculate monetary (total amount in period)
     */
    private function calculateMonetary($orders)
    {
        return $orders->sum('total_amount') ?? 0;
    }

    /**
     * Convert recency to score (1-3)
     */
    private function getRecencyScore($recency)
    {
        if ($recency <= 30) return 3;
        if ($recency <= 90) return 2;
        return 1;
    }

    /**
     * Convert frequency to score (1-3)
     */
    private function getFrequencyScore($frequency)
    {
        if ($frequency >= 6) return 3;
        if ($frequency >= 3) return 2;
        return 1;
    }

    /**
     * Convert monetary to score (1-3)
     */
    private function getMonetaryScore($monetary)
    {
        if ($monetary >= 25000) return 3;
        if ($monetary >= 10000) return 2;
        return 1;
    }

    /**
     * Get RFM segment name based on total score
     */
    private function getRfmSegmentName($totalScore)
    {
        if ($totalScore >= 8) return 'VIP';
        if ($totalScore >= 6) return 'Steady';
        if ($totalScore == 5) return 'Growth';
        if ($totalScore >= 3) return 'At-Risk';
        return 'Dormant';
    }

    /**
     * Get order size segment name based on average quantity
     */
    private function getOrderSizeSegmentName($avgQuantity)
    {
        if ($avgQuantity >= 1000) return 'Bulk Buyers';
        if ($avgQuantity >= 250) return 'Mid-Volume';
        return 'Micro-orders';
    }

    /**
     * Get segment statistics for dashboard
     */
    public function getSegmentStats()
    {
        $rfmStats = DB::table('customer_segment_wholesaler')
            ->join('customer_segments', 'customer_segments.id', '=', 'customer_segment_wholesaler.segment_id')
            ->where('customer_segments.segment_type', 'rfm')
            ->select('customer_segments.name', DB::raw('count(*) as count'))
            ->groupBy('customer_segments.name')
            ->get();

        $orderSizeStats = DB::table('customer_segment_wholesaler')
            ->join('customer_segments', 'customer_segments.id', '=', 'customer_segment_wholesaler.segment_id')
            ->where('customer_segments.segment_type', 'order_size')
            ->select('customer_segments.name', DB::raw('count(*) as count'))
            ->groupBy('customer_segments.name')
            ->get();

        return [
            'rfm' => $rfmStats,
            'order_size' => $orderSizeStats
        ];
    }
} 