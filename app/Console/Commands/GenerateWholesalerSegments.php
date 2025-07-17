<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WholesalerSegmentationService;
use App\Models\Wholesaler;

class GenerateWholesalerSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wholesaler:generate-segments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RFM and order size segments for all wholesalers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting wholesaler segmentation process...');
        
        $segmentationService = new WholesalerSegmentationService();
        
        try {
            // Generate segments
            $segmentationService->generateSegments();
            
            // Get statistics
            $stats = $segmentationService->getSegmentStats();
            
            $this->info('Segmentation completed successfully!');
            $this->newLine();
            
            // Display RFM segment statistics
            $this->info('RFM Segment Distribution:');
            $this->table(['Segment', 'Count'], $stats['rfm']->map(function($item) {
                return [$item->name, $item->count];
            })->toArray());
            
            $this->newLine();
            
            // Display order size segment statistics
            $this->info('Order Size Segment Distribution:');
            $this->table(['Segment', 'Count'], $stats['order_size']->map(function($item) {
                return [$item->name, $item->count];
            })->toArray());
            
            $totalWholesalers = Wholesaler::count();
            $this->info("Total wholesalers processed: {$totalWholesalers}");
            
        } catch (\Exception $e) {
            $this->error('Error during segmentation: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
