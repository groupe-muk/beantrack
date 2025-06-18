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

        return view('sample', compact('salesData', 'salesData2', 'salesCategories', 'productsTableHeaders', 'productsTableData'));
    }

    
}