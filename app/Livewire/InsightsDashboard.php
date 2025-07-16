<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WholesalerSegmentationService;
use App\Models\Wholesaler;

class InsightsDashboard extends Component
{
    public $segmentStats;
    public $totalWholesalers;
    public $lastUpdated;

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
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }

    public function render()
    {
        return view('livewire.insights-dashboard');
    }
}
