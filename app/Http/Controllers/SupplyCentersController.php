<?php

namespace App\Http\Controllers;

use App\Models\SupplyCenter; 
use Illuminate\Http\Request;
use App\Models\Worker;

class SupplyCentersController extends Controller
{

  // app/Http/Controllers/WarehouseController.php
    public function supplycenters()
    {
        $supplycenters = SupplyCenter::with('workers')->get();
        return view('supplycenters.supplycenters', compact('supplycenters'));
    }

    public function store(Request $request)
    {
        SupplyCenter::create($request->validate([
            'name' => 'required',
            'location' => 'required',
            'manager' => 'required',
            'status' => 'required|in:active,inactive',
            'capacity' => 'required|integer|min:1',
        ]));
        return redirect()->back()->with('success', 'Warehouse added successfully!');
    }

    public function update(Request $request, SupplyCenter $supplycenter)
    {
        $supplycenter->update($request->validate([
            'name' => 'required',
            'location' => 'required',
            'manager' => 'required',
            'status' => 'required|in:active,inactive',
            'capacity_lbs' => 'required|integer|min:1',
        ]));
        return redirect()->back()->with('success', 'Warehouse updated successfully!');
    }

    public function destroy(SupplyCenter $supplycenter)
    {
        $supplycenter->delete();
        return redirect()->back()->with('success', 'Warehouse deleted.');
    }

    public function storeworker(Request $request, SupplyCenter $supplycenter)
    {
        $supplycenter->workers()->create($request->validate([
            'name' => 'required',
            'role' => 'required',
            'shift' => 'required',
        ]));
        return redirect()->back()->with('success', 'Staff added successfully!');
    }

    public function updateworker(Request $request, Worker $worker)
    {
        $worker->update($request->validate([
            'name' => 'required',
            'role' => 'required',
            'shift' => 'required',
        ]));
        return redirect()->back()->with('success', 'Staff updated successfully!');
    }
}


