<?php

namespace App\Http\Controllers;

use App\Models\SupplyCenter; 
use App\Models\Worker;
use App\Models\WorkforceAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplyCentersController extends Controller
{
    public function supplycenters(Request $request)
    {
        $supplycenters = SupplyCenter::with('workers')->get();
        
        // If this is an AJAX request for stats update
        if ($request->ajax()) {
            return response()->json([
                'totalSupplyCenters' => $supplycenters->count(),
                'totalStaff' => $supplycenters->sum(fn($sc) => $sc->workers->count()),
                'totalCapacity' => number_format($supplycenters->sum('capacity')) . ' kgs'
            ]);
        }
        
        return view('SupplyCenters.SupplyCenters', compact('supplycenters'));
    }

    public function show($supplycenter)
    {
        $supplycenter = SupplyCenter::with('workers')->findOrFail($supplycenter);
        return view('SupplyCenters.show', compact('supplycenter'));
    }

    public function store(Request $request)
    {
        SupplyCenter::create($request->validate([
            'name' => 'required',
            'location' => 'required',
            'manager' => 'required',
            'capacity' => 'required|integer|min:1',
        ]));
        return redirect()->back()->with('success', 'Supply center added successfully!');
    }

    public function update(Request $request, SupplyCenter $supplycenter)
    {
        $supplycenter->update($request->validate([
            'name' => 'required',
            'location' => 'required',
            'manager' => 'required',
            'capacity' => 'required|integer|min:1',
        ]));
        return redirect()->back()->with('success', 'Supply center updated successfully!');
    }

    public function destroy(SupplyCenter $supplycenter)
    {
        // Delete related workers first (cascade will handle this automatically with foreign key)
        $supplycenter->delete();
        return redirect()->back()->with('success', 'Supply center deleted.');
    }

    public function storeWorker(Request $request, SupplyCenter $supplycenter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
        ]);

        // Create worker directly under the supply center
        $supplycenter->workers()->create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'shift' => $validated['shift'],
            'email' => strtolower(str_replace(' ', '.', $validated['name'])) . '@beantrack.com',
            'phone' => '000-000-0000',
            'address' => 'To be updated'
        ]);

        return redirect()->back()->with('success', 'Staff added successfully!');
    }

    public function updateWorker(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'shift' => 'required|string|in:Morning,Afternoon,Night',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $worker->update($validated);

        return redirect()->back()->with('success', 'Worker updated successfully!');
    }

    public function uploadWorkers(Request $request)
    {
        Log::info('Upload workers method called');
        
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $file = $request->file('excel_file');
            Log::info('File uploaded', ['filename' => $file->getClientOriginalName()]);
            
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            Log::info('Rows extracted from file', ['total_rows' => count($rows)]);

            // Skip header row
            $header = array_shift($rows);
            Log::info('Header row', ['headers' => $header]);
            
            $addedWorkers = 0;
            $errors = [];

            // Get all supply centers for random assignment
            $supplyCenters = SupplyCenter::all();
            Log::info('Supply centers found', ['count' => $supplyCenters->count()]);

            if ($supplyCenters->isEmpty()) {
                return redirect()->back()->with('error', 'No supply centers available for worker assignment.');
            }

            DB::transaction(function () use ($rows, $supplyCenters, &$addedWorkers, &$errors) {
                // For even distribution, track assignments per supply center
                $supplyCenterAssignments = $supplyCenters->pluck('id')->mapWithKeys(function ($id) {
                    return [$id => 0];
                })->toArray();
                
                foreach ($rows as $index => $row) {
                    try {
                        // Skip empty rows
                        if (empty(array_filter($row))) continue;

                        $name = trim($row[0] ?? '');
                        $role = trim($row[1] ?? '');
                        $shift = trim($row[2] ?? '');
                        $email = trim($row[3] ?? '');
                        $phone = trim($row[4] ?? '');
                        $address = trim($row[5] ?? '');

                        Log::info('Processing row', [
                            'row_number' => $index + 2,
                            'name' => $name,
                            'role' => $role,
                            'shift' => $shift
                        ]);

                        if (empty($name) || empty($role) || empty($shift)) {
                            $errors[] = "Row " . ($index + 2) . ": Name, Role, and Shift are required";
                            continue;
                        }

                        // Find supply center with least workers for even distribution
                        $minAssignments = min($supplyCenterAssignments);
                        $availableCenters = array_keys($supplyCenterAssignments, $minAssignments);
                        $selectedCenterId = $availableCenters[array_rand($availableCenters)];
                        $selectedSupplyCenter = $supplyCenters->find($selectedCenterId);
                        
                        Log::info('Assigning to supply center for even distribution', [
                            'supply_center_id' => $selectedCenterId,
                            'current_assignments' => $supplyCenterAssignments[$selectedCenterId]
                        ]);
                        
                        // Create worker and assign to supply center
                        $worker = $selectedSupplyCenter->workers()->create([
                            'name' => $name,
                            'role' => $role,
                            'shift' => $shift,
                            'email' => $email ?: strtolower(str_replace(' ', '.', $name)) . '@beantrack.com',
                            'phone' => $phone ?: '000-000-0000',
                            'address' => $address ?: 'To be updated'
                        ]);
                        
                        // Update assignment count
                        $supplyCenterAssignments[$selectedCenterId]++;
                        
                        Log::info('Worker created', [
                            'worker_id' => $worker->id,
                            'supply_center_id' => $selectedCenterId,
                            'new_assignment_count' => $supplyCenterAssignments[$selectedCenterId]
                        ]);

                        $addedWorkers++;
                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                        Log::error("Error adding worker from spreadsheet", [
                            'row' => $index + 2,
                            'data' => $row,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Upload completed', ['added_workers' => $addedWorkers, 'errors' => count($errors)]);

            $message = "Successfully added {$addedWorkers} workers.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more.";
                }
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error("Error processing worker upload", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to upload workers: ' . $e->getMessage());
        }
    }

    public function moveWorkers(Request $request, $supplycenter)
    {
        $request->validate([
            'destination_supply_center_id' => 'required|exists:supply_centers,id',
            'worker_ids' => 'required|array|min:1',
            'worker_ids.*' => 'exists:workers,id'
        ]);

        try {
            $sourceSupplyCenter = SupplyCenter::findOrFail($supplycenter);
            $destinationSupplyCenter = SupplyCenter::findOrFail($request->destination_supply_center_id);
            
            $workerIds = $request->worker_ids;
            $movedCount = 0;

            DB::transaction(function () use ($workerIds, $destinationSupplyCenter, &$movedCount) {
                foreach ($workerIds as $workerId) {
                    $worker = Worker::find($workerId);
                    if ($worker) {
                        $worker->update(['supplycenter_id' => $destinationSupplyCenter->id]);
                        $movedCount++;
                        
                        Log::info('Worker moved between supply centers', [
                            'worker_id' => $worker->id,
                            'worker_name' => $worker->name,
                            'from_supply_center' => $worker->supplycenter_id,
                            'to_supply_center' => $destinationSupplyCenter->id
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', "Successfully moved {$movedCount} worker(s) to {$destinationSupplyCenter->name}.");

        } catch (\Exception $e) {
            Log::error("Error moving workers between supply centers", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to move workers: ' . $e->getMessage());
        }
    }

    public function bulkDeleteWorkers(Request $request)
    {
        $request->validate([
            'worker_ids' => 'required|array|min:1',
            'worker_ids.*' => 'exists:workers,id'
        ]);

        try {
            DB::beginTransaction();

            $workerIds = $request->worker_ids;
            $deletedCount = 0;

            // Get worker names for logging before deletion
            $workersToDelete = Worker::whereIn('id', $workerIds)->get();
            $workerNames = $workersToDelete->pluck('name')->toArray();

            // Delete the workers
            $deletedCount = Worker::whereIn('id', $workerIds)->delete();

            DB::commit();

            Log::info("Bulk deleted workers", [
                'count' => $deletedCount,
                'worker_names' => $workerNames,
                'deleted_by' => Auth::user()->email ?? 'Unknown'
            ]);

            return redirect()->back()->with('success', "Successfully deleted {$deletedCount} worker(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error bulk deleting workers", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'worker_ids' => $request->worker_ids ?? []
            ]);
            return redirect()->back()->with('error', 'Failed to delete workers: ' . $e->getMessage());
        }
    }
}


