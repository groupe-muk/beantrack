<?php

namespace App\Http\Controllers;

use App\Models\SupplyCenter; 
use Illuminate\Http\Request;

class SupplyCentersController extends Controller
{
    public function shownSupplyCenter1()
    {
         $SupplyCenter = SupplyCenter::with('workers')->findOrFail(1);
         $allSupplyCenters = SupplyCenter::all();
         $managerName = $SupplyCenter->manager_name;
        return view('SupplyCenters.SupplyCenter1' , compact('SupplyCenter', 'allSupplyCenters', 'managerName'));
    }

    public function shownSupplyCenter2()
    {
         $SupplyCenter = SupplyCenter::with('workers')->findOrFail(2);
         $allSupplyCenters = SupplyCenter::all();
         $managerName = $SupplyCenter->manager_name;
        return view('SupplyCenters.SupplyCenter2' , compact('SupplyCenter', 'allSupplyCenters', 'managerName'));
    }
    
     public function shownSupplyCenter3()
    {
         $SupplyCenter = SupplyCenter::with('workers')->findOrFail(3);
         $allSupplyCenters = SupplyCenter::all();
         $managerName = $SupplyCenter->manager_name;
        return view('SupplyCenters.SupplyCenter3' , compact('SupplyCenter', 'allSupplyCenters', 'managerName'));
    }




    
    
}