<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChartTestController extends Controller
{
    public function showColumnChart()
    {
        $salesData = [120, 150, 130, 170, 160, 190, 200];
        $salesData2 = [80, 100, 90, 110, 105, 120, 130];
        $salesCategories = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];


        return view('sample', compact('salesData', 'salesData2', 'salesCategories'));
    }
}