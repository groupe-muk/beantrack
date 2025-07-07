<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class columnChartController extends Controller
{
    public function showColumnChart()
    {
        $salesData = [120, 150, 130, 170, 160, 190, 200];
        $salesData2 = [80, 100, 90, 110, 105, 120, 130];
        $salesCategories = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

         // --- Table Data for Products ---
        $productsTableHeaders = ['Product Name', 'Price (UGX)', 'Stock', 'Status'];
        $productsTableData = [
            ['Product Name' => 'Ugandan Coffee Beans', 'Price (UGX)' => '25,000', 'Stock' => 150, 'Status' => 'In Stock'],
            ['Product Name' => 'Organic Tea Leaves', 'Price (UGX)' => '12,500', 'Stock' => 0, 'Status' => 'Out of Stock'],
            ['Product Name' => 'Local Honey', 'Price (UGX)' => '18,000', 'Stock' => 75, 'Status' => 'Limited Stock'],
            ['Product Name' => 'Dried Fruits Mix', 'Price (UGX)' => '30,000', 'Stock' => 200, 'Status' => 'In Stock'],
        ];

             $inventoryItems = [
            [
                'name' => 'Arabica Grade A',
                'available' => 1000,
                'allocated' => 1340,
                'statusLabel' => 'Healthy',
            ],
            [
                'name' => 'Arabica Grade B',
                'available' => 400,
                'allocated' => 640,
                'statusLabel' => 'Healthy',
            ],
            [
                'name' => 'Robusta Grade A',
                'available' => 150,
                'allocated' => 360,
                'statusLabel' => 'Low',
            ],
            [
                'name' => 'Colombia Supremo',
                'available' => 20,
                'allocated' => 180,
                'statusLabel' => 'Critical',
            ],
        ];

        $pendingOrders = [
            [
                'name' => 'Coffee House Roasters',
                'order_id' => 'CMD-1842',
                'quantity' => 200,
                'date' => '2025-05-28',
                'productName' => 'Arabica Grade A',
            ],
            [
                'name' => 'Bean & Brew Inc.',
                'order_id' => 'ES-903',
                'quantity' => 180,
                'date' => '2025-06-03',
                'productName' => 'Arabica Medium Roast',
            ],
            [
                'name' => 'Fresh Roast Co.',
                'order_id' => 'FRC-777',
                'quantity' => 500,
                'date' => '2025-06-15',
                'productName' => 'Robusta Grade A',
            ],
        ];

             $mlPredictionData = [
            [
                'name' => 'Actual',
                'data' => [50, 55, 60, 58, 65, 70, 68, 75, 80, 82, 85, 90]
            ],
            [
                'name' => 'Predicted',
                'data' => [20, 25, 28, 35, 30, 45, 50, 60, 70, 65, 75, 80]
            ],
            
            [
                'name' => 'Optimisstic',
                'data' => [30, 40, 35, 50, 49, 60, 70, 91, 125, 100, 110, 130]
            ]      
        ];

        $mlPredictionCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $mlPredictionDescription = '';

        $lineChartData = [
        [
            'name' => 'Arabica',
            'data' => [50, 55, 60, 58, 65, 70]
        ],
        [
            'name' => 'Robusta',
            'data' => [20, 25, 28, 35, 30, 45]
        ],
            
        [
            'name' => 'Excella',
            'data' => [30, 40, 35, 50, 49, 60]
        ]      
        ];
        
        $lineChartCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];

        return view('sample', compact('salesData', 'salesData2', 'salesCategories', 'productsTableHeaders', 'productsTableData', 'inventoryItems', 'pendingOrders', 'mlPredictionData', 'mlPredictionCategories', 'mlPredictionDescription', 'lineChartData', 'lineChartCategories' ));
        
    }

    
}