<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Report;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\Supplier;
use Laravel\Sanctum\HasApiTokens;
use App\Services\DemandForecastService;
use Carbon\Carbon;


class dashboardController extends Controller
{
    
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('onboarding'); // Ensure user is authenticated
        }

        $data = []; // Initialize an empty array to hold all data for the view
        $user = Auth::user();

        // Get selected product for ML predictions (Admin only)
        $selectedProduct = null;
        $allProducts = collect();
        if ($user->isAdmin()) {
            $selectedProduct = CoffeeProduct::find($request->input('product_id'))
                             ?? CoffeeProduct::first();
            $allProducts = CoffeeProduct::with('rawCoffee')->get();
        }

        if ($user->isAdmin()) {
            // Fetch data specifically for the Admin dashboard
            $data = array_merge($data, $this->getAdminDashboardData($selectedProduct));
            $data['products'] = $allProducts;
            $data['currentProductId'] = $selectedProduct ? $selectedProduct->id : null;
        } elseif ($user->isSupplier()) {
            // Fetch data specifically for the Supplier dashboard
            $data = array_merge($data, $this->getSupplierDashboardData());
        } elseif ($user->isVendor()) {
            // Fetch data specifically for the Vendor dashboard
            $data = array_merge($data, $this->getVendorDashboardData());
        }

        // Add any common dashboard data here if applicable to all roles
        // $data['commonMetric'] = someCommonService::getCommonMetric();

        return view('dashboard', $data); // Pass all collected data to the main dashboard view
    }

    /**
     * Get ML prediction chart data via AJAX
     */
    public function getChartData(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $productId = $request->input('product_id');
        $product = CoffeeProduct::find($productId);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $forecastData = $this->getDemandForecastChartData($product);
        
        return response()->json([
            'series' => $forecastData['series'],
            'categories' => $forecastData['categories'],
            'productName' => $product->name
        ]);
    }

    
    private function getAdminDashboardData(?CoffeeProduct $selectedProduct = null): array
    {
        // Build chart for the selected product
        $forecastData = $selectedProduct ? $this->getDemandForecastChartData($selectedProduct) : ['series' => [], 'categories' => []];

        return [
            'mlPredictionData' => $forecastData['series'],
            'mlPredictionCategories' => $forecastData['categories'],
            'mlPredictionDescription' => '',

            'productsTableHeaders' => ['Order ID', 'Customer', 'Product', 'Quantity (kg)', 'Status', 'Date'],
            'productsTableData' => $this->getRecentOrdersForTable(4),

            'lineChartData' => $this->getDefectCountData()['data'],
            'lineChartCategories' => $this->getDefectCountData()['categories'],

            'pendingOrders' => $this->getPendingOrders(2),
            'inventoryData' => $this->getInventoryData()['rawCoffeeData'],
            'inventoryData2' => $this->getInventoryData()['coffeeProductData'],
            'inventoryCategories' => $this->getInventoryData()['categories'],

            // Recent reports data
            'recentReports' => $this->getRecentReports(2),

        ];   

    }

 
    private function getSupplierDashboardData(): array
    {
        return [
        'pendingOrders' => [
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
                'name' => 'Coffee House Roasters',
                'order_id' => 'CMD-1842',
                'quantity' => 200,
                'date' => '2025-05-28',
                'productName' => 'Arabica Grade A',
            ],
        ],
        'productsTableHeaders' => ['Product Name', 'Price (UGX)', 'Stock', 'Status'],
            'productsTableData' => [
            ['Product Name' => 'Ugandan Coffee Beans', 'Price (UGX)' => '25,000', 'Stock' => 150, 'Status' => 'In Stock'],
            ['Product Name' => 'Organic Tea Leaves', 'Price (UGX)' => '12,500', 'Stock' => 0, 'Status' => 'Out of Stock'],
            ['Product Name' => 'Local Honey', 'Price (UGX)' => '18,000', 'Stock' => 75, 'Status' => 'Limited Stock'],
            ['Product Name' => 'Dried Fruits Mix', 'Price (UGX)' => '30,000', 'Stock' => 200, 'Status' => 'In Stock'],
        ],
        
        'inventoryItems' => [
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
        ],

        ]; 
    }


    private function getVendorDashboardData(): array
    {
        return [
            'pendingOrders' => [
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
            ],
            
            'inventoryItems' => [
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
        ],
        
        // Recent reports data for vendor
        'recentReports' => $this->getRecentReportsForVendor(2),
        ]; 
    }

    /**
     * Get defect count data for line chart display
     */
    private function getDefectCountData(): array
    {
        try {
            // Get data from the last 6 months grouped by coffee type and month
            $sixMonthsAgo = now()->subMonths(6);
            
            $rawCoffeeData = RawCoffee::select('coffee_type', 'defect_count', 'created_at')
                ->where('created_at', '>=', $sixMonthsAgo)
                ->orderBy('created_at', 'asc')
                ->get();

            // Group data by coffee type and month
            $groupedData = [];
            $categories = [];
            
            // Generate month categories for the last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $categories[] = $month->format('M Y');
            }

            // Initialize data structure for each coffee type
            $coffeeTypes = $rawCoffeeData->pluck('coffee_type')->unique()->toArray();
            
            foreach ($coffeeTypes as $type) {
                $groupedData[$type] = array_fill(0, 6, 0); // Initialize with zeros for 6 months
            }

            // Aggregate defect counts by coffee type and month
            foreach ($rawCoffeeData as $coffee) {
                $monthIndex = now()->diffInMonths($coffee->created_at);
                if ($monthIndex < 6) {
                    $arrayIndex = 5 - $monthIndex; // Reverse index for chronological order
                    if (isset($groupedData[$coffee->coffee_type])) {
                        $groupedData[$coffee->coffee_type][$arrayIndex] += $coffee->defect_count ?? 0;
                    }
                }
            }

            // Format data for chart
            $chartData = [];
            foreach ($groupedData as $coffeeType => $data) {
                $chartData[] = [
                    'name' => ucfirst($coffeeType),
                    'data' => array_values($data)
                ];
            }

            // Ensure we have at least some data to display
            if (empty($chartData)) {
                $chartData = [
                    [
                        'name' => 'Arabica',
                        'data' => [15, 12, 18, 10, 8, 14]
                    ],
                    [
                        'name' => 'Robusta',
                        'data' => [8, 10, 12, 7, 5, 9]
                    ]
                ];
            }

            return [
                'data' => $chartData,
                'categories' => $categories
            ];

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
                'data' => [
                    [
                        'name' => 'Arabica',
                        'data' => [15, 12, 18, 10, 8, 14]
                    ],
                    [
                        'name' => 'Robusta',
                        'data' => [8, 10, 12, 7, 5, 9]
                    ],
                    [
                        'name' => 'Excella',
                        'data' => [5, 7, 9, 4, 3, 6]
                    ]
                ],
                'categories' => ['Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025']
            ];
        }
    }

    /**
     * Get pending orders from the database
     */
    private function getPendingOrders($limit = 2): array
    {
        try {
            $orders = Order::with(['supplier', 'wholesaler', 'rawCoffee', 'coffeeProduct'])
                ->where('status', 'pending')
                ->orderBy('order_date', 'desc')
                ->limit($limit)
                ->get();

            return $orders->map(function ($order) {
                // Determine customer name (supplier or wholesaler)
                $customerName = $order->supplier ? $order->supplier->name : 
                               ($order->wholesaler ? $order->wholesaler->name : 'Unknown Customer');
                
                // Determine product name
                $productName = $order->rawCoffee ? $order->rawCoffee->coffee_type : 
                              ($order->coffeeProduct ? $order->coffeeProduct->name : 'Unknown Product');

                return [
                    'name' => $customerName,
                    'order_id' => $order->id,
                    'quantity' => number_format($order->quantity, 0),
                    'date' => $order->order_date ? $order->order_date->format('Y-m-d') : $order->created_at->format('Y-m-d'),
                    'productName' => $productName,
                ];
            })->toArray();

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
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
            ];
        }
    }

    /**
     * Get recent orders for table display
     */
    private function getRecentOrdersForTable($limit = 4): array
    {
        try {
            $orders = Order::with(['supplier', 'wholesaler', 'rawCoffee', 'coffeeProduct'])
                ->orderBy('order_date', 'desc')
                ->limit($limit)
                ->get();

            return $orders->map(function ($order) {
                // Determine customer name (supplier or wholesaler)
                $customerName = $order->supplier ? $order->supplier->name : 
                               ($order->wholesaler ? $order->wholesaler->name : 'Unknown Customer');
                
                // Determine product name
                $productName = $order->rawCoffee ? $order->rawCoffee->coffee_type : 
                              ($order->coffeeProduct ? $order->coffeeProduct->name : 'Unknown Product');

                // Format status for display
                $status = ucfirst($order->status);
                
                // Format quantity with unit
                $quantity = number_format($order->quantity, 0) . ' kg';

                return [
                    'Order ID' => $order->id,
                    'Customer' => $customerName,
                    'Product' => $productName,
                    'Quantity (kg)' => $quantity,
                    'Status' => $status,
                    'Date' => $order->order_date ? $order->order_date->format('M d, Y') : $order->created_at->format('M d, Y'),
                ];
            })->toArray();

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
                [
                    'Order ID' => 'O00001',
                    'Customer' => 'Coffee House Roasters',
                    'Product' => 'Arabica Grade A',
                    'Quantity (kg)' => '200 kg',
                    'Status' => 'Delivered',
                    'Date' => 'Jun 28, 2025',
                ],
                [
                    'Order ID' => 'O00002',
                    'Customer' => 'Bean & Brew Inc.',
                    'Product' => 'Robusta Premium',
                    'Quantity (kg)' => '150 kg',
                    'Status' => 'Shipped',
                    'Date' => 'Jun 27, 2025',
                ],
                [
                    'Order ID' => 'O00003',
                    'Customer' => 'Local Coffee Shop',
                    'Product' => 'Arabica Medium Roast',
                    'Quantity (kg)' => '75 kg',
                    'Status' => 'Confirmed',
                    'Date' => 'Jun 26, 2025',
                ],
                [
                    'Order ID' => 'O00004',
                    'Customer' => 'Wholesale Distributors',
                    'Product' => 'Mixed Coffee Blend',
                    'Quantity (kg)' => '300 kg',
                    'Status' => 'Pending',
                    'Date' => 'Jun 25, 2025',
                ],
            ];
        }
    }

    /**
     * Get the most recent reports for dashboard display
     */
    private function getRecentReports($limit = 2): array
    {
        try {
            // Get the most recent reports that have been sent/generated
            // Filter to only show admin-created reports (not supplier/vendor reports)
            $reports = Report::whereNotNull('last_sent')
                ->where(function($query) {
                    $query->whereHas('creator', function($userQuery) {
                        $userQuery->where('role', 'admin');
                    })->orWhereNull('created_by'); // Include legacy reports without creator
                })
                ->orderBy('last_sent', 'desc')
                ->limit($limit)
                ->get();

            \Log::info('Admin recent reports query (with last_sent)', [
                'count' => $reports->count(),
                'report_ids' => $reports->pluck('id')->toArray(),
                'report_creators' => $reports->pluck('created_by')->toArray()
            ]);

            // If no reports with last_sent, fall back to recently created reports
            if ($reports->isEmpty()) {
                $reports = Report::where(function($query) {
                        $query->whereHas('creator', function($userQuery) {
                            $userQuery->where('role', 'admin');
                        })->orWhereNull('created_by'); // Include legacy reports without creator
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
                    
                \Log::info('Admin recent reports query (by created_at)', [
                    'count' => $reports->count(),
                    'report_ids' => $reports->pluck('id')->toArray(),
                    'report_creators' => $reports->pluck('created_by')->toArray()
                ]);
            }

            return $reports->map(function ($report) {
                // Parse recipients and convert to names
                $recipientNames = $this->parseRecipientsToNames($report->recipients);
                
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'date_generated' => $report->last_sent ?? $report->created_at,
                    'recipients' => $recipientNames,
                    'status' => $report->status ?? 'completed',
                    'format' => $report->format ?? 'pdf',
                ];
            })->toArray();

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
                [
                    'id' => 'R00001',
                    'name' => 'Monthly Supplier Demand Forecast',
                    'date_generated' => now()->subHours(2),
                    'recipients' => 'Finance Dept, Logistics Team',
                    'status' => 'completed',
                    'format' => 'pdf',
                ],
                [
                    'id' => 'R00002',
                    'name' => 'Weekly Production Efficiency',
                    'date_generated' => now()->subDay(),
                    'recipients' => 'Production Team',
                    'status' => 'completed',
                    'format' => 'excel',
                ],
            ];
        }
    }

    /**
     * Get recent reports for vendor
     */
    private function getRecentReportsForVendor($limit = 2): array
    {
        try {
            $userId = Auth::id();
            
            // Get the most recent reports that have been sent/generated for this vendor
            $reports = Report::whereNotNull('last_sent')
                ->where('created_by', $userId)
                ->orderBy('last_sent', 'desc')
                ->limit($limit)
                ->get();

            // If no reports with last_sent, fall back to recently created reports
            if ($reports->isEmpty()) {
                $reports = Report::where('created_by', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
            }

            return $reports->map(function ($report) {
                // Parse recipients and convert to names
                $recipientNames = $this->parseRecipientsToNames($report->recipients);
                
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'date_generated' => $report->last_sent ?? $report->created_at,
                    'recipients' => $recipientNames,
                    'status' => $report->status ?? 'completed',
                    'format' => $report->format ?? 'pdf',
                ];
            })->toArray();

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
                [
                    'id' => 'R00001',
                    'name' => 'Vendor Purchases Report',
                    'date_generated' => now()->subHours(2),
                    'recipients' => 'Vendor Dashboard',
                    'status' => 'completed',
                    'format' => 'pdf',
                ],
                [
                    'id' => 'R00002',
                    'name' => 'Vendor Inventory Report',
                    'date_generated' => now()->subDay(),
                    'recipients' => 'Vendor Dashboard',
                    'status' => 'completed',
                    'format' => 'excel',
                ],
            ];
        }
    }

    /**
     * Get inventory data for column chart display
     */
    private function getInventoryData(): array
    {
        try {
            // Get current inventory levels for raw coffee and coffee products
            $rawCoffeeInventory = Inventory::with(['rawCoffee'])
                ->whereNotNull('raw_coffee_id')
                ->get();

            $coffeeProductInventory = Inventory::with(['coffeeProduct'])
                ->whereNotNull('coffee_product_id')
                ->get();

            // Generate categories for the last 7 days
            $categories = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $categories[] = $date->format('M d');
            }

            // Calculate total inventory levels
            $rawCoffeeTotal = $rawCoffeeInventory->sum('quantity_in_stock');
            $coffeeProductTotal = $coffeeProductInventory->sum('quantity_in_stock');

            // Create realistic data trends over 7 days
            $rawCoffeeData = [];
            $coffeeProductData = [];
            
            for ($i = 0; $i < 7; $i++) {
                // Add slight variations around the base values to simulate daily changes
                $rawCoffeeData[$i] = max(0, $rawCoffeeTotal + rand(-20, 20));
                $coffeeProductData[$i] = max(0, $coffeeProductTotal + rand(-15, 15));
            }

            return [
                'rawCoffeeData' => $rawCoffeeData,
                'coffeeProductData' => $coffeeProductData,
                'categories' => $categories
            ];

        } catch (\Exception $e) {
            // Return mock data if database query fails
            return [
                'rawCoffeeData' => [120, 150, 130, 170, 160, 190, 200],
                'coffeeProductData' => [80, 100, 90, 110, 105, 120, 130],
                'categories' => ['Jun 24', 'Jun 25', 'Jun 26', 'Jun 27', 'Jun 28', 'Jun 29', 'Jun 30']
            ];
        }
    }

    /**
     * Parse recipients field and convert user IDs to names
     */
    private function parseRecipientsToNames($recipients): string
    {
        if (!$recipients) {
            return 'Not specified';
        }

        try {
            $recipientIds = [];
            
            // Handle different formats of recipients data
            if (is_string($recipients)) {
                // Try parsing as JSON first
                $decoded = json_decode($recipients, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $recipientIds = $decoded;
                } else {
                    // Try parsing as comma-separated string
                    $recipientIds = array_map('trim', explode(',', $recipients));
                }
            } elseif (is_array($recipients)) {
                $recipientIds = $recipients;
            } else {
                // Single recipient
                $recipientIds = [$recipients];
            }

            // Filter out empty values
            $recipientIds = array_filter($recipientIds, function($id) {
                return !empty($id);
            });

            if (empty($recipientIds)) {
                return 'Not specified';
            }

            // Get user names from database
            $users = User::whereIn('id', $recipientIds)
                         ->select('id', 'name')
                         ->get()
                         ->keyBy('id');

            $names = [];
            foreach ($recipientIds as $id) {
                if (isset($users[$id])) {
                    $names[] = $users[$id]->name;
                } else {
                    // If user not found, show the ID
                    $names[] = "User #{$id}";
                }
            }

            return implode(', ', $names);
            
        } catch (\Exception $e) {
            // If anything goes wrong, return the original value
            return is_string($recipients) ? $recipients : 'Not specified';
        }
    }

    private function getDemandForecastChartData(CoffeeProduct $product, int $historyDays = 14): array
    {
        if (!$product) {
            return ['series' => [], 'categories' => []];
        }

        /** @var DemandForecastService $service */
        $service = app(DemandForecastService::class);

        // 1. Last 14 days (2 weeks) of actual demand
        $history = \App\Models\DemandHistory::where('coffee_product_id', $product->id)
            ->where('demand_date', '>=', now()->subDays($historyDays)->toDateString())
            ->orderBy('demand_date')
            ->get(['demand_date', 'demand_qty_tonnes']);

        if ($history->isEmpty()) {
            return ['series' => [], 'categories' => []];
        }

        // 2. Fetch or generate 7-day forecast starting tomorrow
        $forecast = $service->getLatestForecast($product);
        if ($forecast->isEmpty()) {
            try {
                $forecast = $service->generateAndStoreForecast($product);
            } catch (\Throwable $e) {
                \Log::warning('Unable to fetch/generate demand forecast', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Combine into unified axis
        $categories = [];
        $actualData = [];
        $predictedData = [];

        foreach ($history as $row) {
            $categories[]   = Carbon::parse($row->demand_date)->format('M d');
            $actualData[]   = (float) $row->demand_qty_tonnes;
            $predictedData[] = null; // no prediction for past dates
        }

        foreach ($forecast as $row) {
            $categories[]   = Carbon::parse($row->predicted_date)->format('M d');
            $actualData[]   = null; // no actual future data
            $predictedData[] = (float) $row->predicted_demand_tonnes;
        }

        $series = [
            ['name' => 'Actual',    'data' => $actualData],
            ['name' => 'Predicted', 'data' => $predictedData],
        ];

        return ['series' => $series, 'categories' => $categories];
    }
}