<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Worker;
use App\Models\Supplier;
use App\Models\Wholesaler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WarehouseController extends Controller
{
    public function supplierIndex()
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return redirect()->route('dashboard')->with('error', 'Supplier profile not found.');
        }

        $warehouses = Warehouse::where('supplier_id', $supplier->id)->with('workers')->get();
        
        return view('warehouses.supplier', compact('warehouses', 'supplier'));
    }

    public function vendorIndex()
    {
        $user = Auth::user();
        $wholesaler = Wholesaler::where('user_id', $user->id)->first();
        
        if (!$wholesaler) {
            return redirect()->route('dashboard')->with('error', 'Vendor profile not found.');
        }

        $warehouses = Warehouse::where('wholesaler_id', $wholesaler->id)->with('workers')->get();
        
        return view('warehouses.vendor', compact('warehouses', 'wholesaler'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'capacity' => 'required|numeric|min:1',
        ]);

        if ($user->role === 'supplier') {
            $supplier = Supplier::where('user_id', $user->id)->first();
            if (!$supplier) {
                return redirect()->back()->with('error', 'Supplier profile not found.');
            }
            $validated['supplier_id'] = $supplier->id;
        } elseif ($user->role === 'vendor') {
            $wholesaler = Wholesaler::where('user_id', $user->id)->first();
            if (!$wholesaler) {
                return redirect()->back()->with('error', 'Vendor profile not found.');
            }
            $validated['wholesaler_id'] = $wholesaler->id;
        }

        Warehouse::create($validated);
        
        return redirect()->back()->with('success', 'Warehouse added successfully!');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorizeWarehouse($warehouse);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'capacity' => 'required|numeric|min:1',
        ]);

        $warehouse->update($validated);
        
        return redirect()->back()->with('success', 'Warehouse updated successfully!');
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorizeWarehouse($warehouse);
        
        // Delete related workers first (cascade will handle this automatically with foreign key)
        $warehouse->delete();
        
        return redirect()->back()->with('success', 'Warehouse deleted successfully!');
    }

    public function storeWorker(Request $request, Warehouse $warehouse)
    {
        $this->authorizeWarehouse($warehouse);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
        ]);

        // Create worker directly under the warehouse
        $warehouse->workers()->create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'shift' => $validated['shift'],
            'email' => strtolower(str_replace(' ', '.', $validated['name'])) . '@beantrack.com',
            'phone' => '000-000-0000',
            'address' => 'To be updated'
        ]);

        return redirect()->back()->with('success', 'Worker added successfully!');
    }

    public function updateWorker(Request $request, Worker $worker)
    {
        $warehouse = $worker->warehouse;
        if ($warehouse) {
            $this->authorizeWarehouse($warehouse);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
        ]);

        $worker->update($validated);

        return redirect()->back()->with('success', 'Worker updated successfully!');
    }

    public function uploadWorkers(Request $request)
    {
        $request->validate([
            'worker_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        $user = Auth::user();
        
        // Get user's warehouses
        $warehouses = collect();
        if ($user->role === 'supplier') {
            $supplier = Supplier::where('user_id', $user->id)->first();
            if ($supplier) {
                $warehouses = Warehouse::where('supplier_id', $supplier->id)->get();
            }
        } elseif ($user->role === 'vendor') {
            $wholesaler = Wholesaler::where('user_id', $user->id)->first();
            if ($wholesaler) {
                $warehouses = Warehouse::where('wholesaler_id', $wholesaler->id)->get();
            }
        }

        if ($warehouses->isEmpty()) {
            return redirect()->back()->with('error', 'No warehouses found. Please create warehouses first.');
        }

        try {
            $file = $request->file('worker_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $header = array_shift($rows);
            
            $addedWorkers = 0;
            $errors = [];

            DB::transaction(function () use ($rows, $warehouses, &$addedWorkers, &$errors) {
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

                        if (empty($name) || empty($role)) {
                            $errors[] = "Row " . ($index + 2) . ": Name and Role are required";
                            continue;
                        }

                        // Randomly assign to warehouse
                        $randomWarehouse = $warehouses->random();
                        
                        // Create worker directly under warehouse
                        $randomWarehouse->workers()->create([
                            'name' => $name,
                            'role' => $role,
                            'shift' => $shift ?: 'Day',
                            'email' => $email ?: strtolower(str_replace(' ', '.', $name)) . '@beantrack.com',
                            'phone' => $phone ?: '000-000-0000',
                            'address' => $address ?: 'To be updated'
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

            $message = "Successfully added {$addedWorkers} workers to warehouses.";
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

    private function authorizeWarehouse(Warehouse $warehouse)
    {
        $user = Auth::user();
        
        if ($user->role === 'supplier') {
            $supplier = Supplier::where('user_id', $user->id)->first();
            if (!$supplier || $warehouse->supplier_id !== $supplier->id) {
                abort(403, 'Unauthorized access to warehouse.');
            }
        } elseif ($user->role === 'vendor') {
            $wholesaler = Wholesaler::where('user_id', $user->id)->first();
            if (!$wholesaler || $warehouse->wholesaler_id !== $wholesaler->id) {
                abort(403, 'Unauthorized access to warehouse.');
            }
        } else {
            abort(403, 'Unauthorized access to warehouse.');
        }
    }
}
