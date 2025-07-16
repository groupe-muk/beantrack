<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WholesalerSegmentationService;
use App\Models\Wholesaler;
use App\Models\CustomerSegment;
use Illuminate\Support\Facades\DB;

class InsightsDashboard extends Component
{
    public $segmentStats;
    public $totalWholesalers;
    public $lastUpdated;
    public $wholesalerDetails;
    public $rfmFilter = '';
    public $orderSizeFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $segmentationService = new WholesalerSegmentationService();
        $this->segmentStats = $segmentationService->getSegmentStats();
        $this->totalWholesalers = Wholesaler::count();
        $this->lastUpdated = now()->format('M d, Y \a\t H:i');
        $this->loadWholesalerDetails();
    }

    public function loadWholesalerDetails()
    {
        $query = Wholesaler::with(['segments' => function($query) {
            $query->withPivot('scores');
        }])->select('id', 'name', 'email', 'distribution_region', 'created_at');

        // Apply RFM filter
        if ($this->rfmFilter) {
            $query->whereHas('segments', function($q) {
                $q->where('name', $this->rfmFilter)
                  ->where('segment_type', 'rfm');
            });
        }

        // Apply order size filter
        if ($this->orderSizeFilter) {
            $query->whereHas('segments', function($q) {
                $q->where('name', $this->orderSizeFilter)
                  ->where('segment_type', 'order_size');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $this->wholesalerDetails = $query->get()->map(function($wholesaler) {
            $rfmSegment = $wholesaler->segments->where('segment_type', 'rfm')->first();
            $orderSizeSegment = $wholesaler->segments->where('segment_type', 'order_size')->first();
            
            return [
                'id' => $wholesaler->id,
                'name' => $wholesaler->name,
                'email' => $wholesaler->email,
                'region' => $wholesaler->distribution_region,
                'rfm_segment' => $rfmSegment ? $rfmSegment->name : 'Unassigned',
                'rfm_scores' => $rfmSegment ? json_decode($rfmSegment->pivot->scores, true) : null,
                'order_size_segment' => $orderSizeSegment ? $orderSizeSegment->name : 'Unassigned',
                'order_size_scores' => $orderSizeSegment ? json_decode($orderSizeSegment->pivot->scores, true) : null,
                'joined_date' => $wholesaler->created_at->format('M d, Y'),
            ];
        });
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }

    public function updatedRfmFilter()
    {
        $this->loadWholesalerDetails();
    }

    public function updatedOrderSizeFilter()
    {
        $this->loadWholesalerDetails();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->loadWholesalerDetails();
    }

    public function clearFilters()
    {
        $this->rfmFilter = '';
        $this->orderSizeFilter = '';
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->loadWholesalerDetails();
    }

    public function render()
    {
        return view('livewire.insights-dashboard');
    }
}
