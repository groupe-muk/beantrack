<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;

class WorkerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supply_center_id' => 'required|exists:supply_centers,id',
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
        ]);

        Worker::create($validated);
        return redirect()->route('show.supplycenter2')->with('success', 'Staff added successfully');
    }

    public function update(Request $request, Worker $worker)
    {
        $request->validate([
            'supply_center_id' => 'required|exists:supply_centers,id',
            'name' => 'required',
            'role' => 'required',
            'shift' => 'required',
        ]);

        $worker->update($request->all());
        return redirect()->back()->with('success', 'Staff updated successfully');
    }
    public function destroy(Worker $worker)
    {
        $worker->delete();
        return redirect()->back()->with('success', 'Staff deleted successfully');
    }
}