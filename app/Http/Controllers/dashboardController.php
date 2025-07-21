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
use App\Models\SupplyCenter;
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

        // Get real-time statistics
        $adminStats = $this->getAdminStats();

        return [
            // Stats data
            'adminStats' => $adminStats,

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

            // Dynamic inventory items for progress card
            'inventoryItems' => $this->getAdminInventoryItems(),

        ];   

    }

 
    private function getSupplierDashboardData(): array
    {
        // Get real pending orders for this supplier
        $pendingOrders = $this->getPendingOrders(4); // Get up to 4 pending orders
        
        // Get real-time statistics
        $supplierStats = $this->getSupplierStats();
        
        return [
            // Stats data
            'supplierStats' => $supplierStats,

            'pendingOrders' => $pendingOrders,
            'productsTableHeaders' => ['Order ID', 'Customer', 'Product', 'Quantity (kg)', 'Status', 'Date'],
            'productsTableData' => $this->getSupplierRecentOrders(),
        
            'inventoryItems' => $this->getSupplierInventoryItems(),

        ]; 
    }


    private function getVendorDashboardData(): array
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        // Get actual pending orders for this vendor
        $pendingOrders = collect();
        if ($wholesaler) {
            $pendingOrders = Order::with(['coffeeProduct'])
                ->where('wholesaler_id', $wholesaler->id)
                ->where('status', 'pending')
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($order) {
                    return [
                        'name' => $order->coffeeProduct->name ?? 'Unknown Product',
                        'order_id' => $order->id,
                        'quantity' => $order->quantity,

                        'date' => $order->created_at->format('Y-m-d'),
                        'productName' => $order->coffeeProduct->name ?? 'Unknown Product',
                        'status' => $order->status,
                        'order_type' => 'made', // Vendor made these orders
                    ];
                });
        }
        
        // Return empty collection if no orders - let the component handle the empty state
        
        // Get real-time statistics
        $vendorStats = $this->getVendorStats();

        return [
            // Stats data
            'vendorStats' => $vendorStats,

            'pendingOrders' => $pendingOrders->toArray(),
            
            'inventoryItems' => $this->getVendorInventoryItems(),
        
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
            $user = Auth::user();
            
            if ($user->isAdmin()) {
                // Admin (Factory) receives orders from vendors (wholesalers) for coffee products
                $orders = Order::with(['wholesaler', 'coffeeProduct'])
                    ->whereNotNull('wholesaler_id')
                    ->where('status', 'pending')
                    ->orderBy('order_date', 'desc')
                    ->limit($limit)
                    ->get();
                    
                return $orders->map(function ($order) {
                    return [
                        'name' => $order->wholesaler ? $order->wholesaler->name : 'Unknown Vendor',
                        'order_id' => $order->id,
                        'quantity' => number_format((float)$order->quantity, 0),
                        'date' => $order->created_at->format('Y-m-d'),
                        'productName' => $order->coffeeProduct ? $order->coffeeProduct->name : 'Unknown Product',
                        'status' => $order->status,
                        'order_type' => 'received', // Admin receives these orders
                    ];
                })->toArray();
                
            } elseif ($user->isSupplier()) {
                // Supplier receives orders from admin (factory) for raw coffee
                $supplierId = $user->supplier ? $user->supplier->id : null;
                if (!$supplierId) {
                    return [];
                }
                
                $orders = Order::with(['supplier', 'rawCoffee'])
                    ->whereNotNull('supplier_id')
                    ->where('supplier_id', $supplierId)
                    ->where('status', 'pending')
                    ->orderBy('order_date', 'desc')
                    ->limit($limit)
                    ->get();
                    
                return $orders->map(function ($order) {
                    return [
                        'name' => 'Factory Order',
                        'order_id' => $order->id,
                        'quantity' => number_format((float)$order->quantity, 0),
                        'date' => $order->created_at->format('Y-m-d'),
                        'productName' => $order->rawCoffee ? $order->rawCoffee->coffee_type : 'Unknown Product',
                        'status' => $order->status,
                        'order_type' => 'received', // Supplier receives these orders
                    ];
                })->toArray();
                
            } elseif ($user->isVendor()) {
                // Vendor (wholesaler) makes orders to admin (factory) for coffee products
                $wholesalerId = $user->wholesaler ? $user->wholesaler->id : null;
                if (!$wholesalerId) {
                    return [];
                }
                
                $orders = Order::with(['wholesaler', 'coffeeProduct'])
                    ->whereNotNull('wholesaler_id')
                    ->where('wholesaler_id', $wholesalerId)
                    ->where('status', 'pending')
                    ->orderBy('order_date', 'desc')
                    ->limit($limit)
                    ->get();
                    
                return $orders->map(function ($order) {
                    return [
                        'name' => $order->coffeeProduct ? $order->coffeeProduct->name : 'Unknown Product',
                        'order_id' => $order->id,
                        'quantity' => number_format((float)$order->quantity, 0),
                        'date' => $order->created_at->format('Y-m-d'),
                        'productName' => $order->coffeeProduct ? $order->coffeeProduct->name : 'Unknown Product',
                        'status' => $order->status,
                        'order_type' => 'made', // Vendor made these orders
                    ];
                })->toArray();
            }
            
            return [];

        } catch (\Exception $e) {
            // Return mock data if database query fails based on user role
            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return [
                    [
                        'name' => 'Coffee House Roasters',
                        'order_id' => 'CMD-1842',
                        'quantity' => 200,
                        'date' => '2025-05-28',
                        'productName' => 'Arabica Grade A',
                        'status' => 'pending',
                        'order_type' => 'received',
                    ],
                ];
            } elseif ($user->isSupplier()) {
                return [
                    [
                        'name' => 'Factory Order',
                        'order_id' => 'CMD-1843',
                        'quantity' => 500,
                        'date' => '2025-05-29',
                        'productName' => 'Arabica Beans',
                        'status' => 'pending',
                        'order_type' => 'received',
                    ],
                ];
            } elseif ($user->isVendor()) {
                return [
                    [
                        'name' => 'Premium Blend',
                        'order_id' => 'CMD-1844',
                        'quantity' => 100,
                        'date' => '2025-05-30',
                        'productName' => 'Premium Blend',
                        'status' => 'pending',
                        'order_type' => 'made',
                    ],
                ];
            }
            
            return [];
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
                $quantity = number_format((float)$order->quantity, 0) . ' kg';

                return [
                    'Order ID' => $order->id,
                    'Customer' => $customerName,
                    'Product' => $productName,
                    'Quantity (kg)' => $quantity,
                    'Status' => $status,
                    'Date' => $order->created_at->format('M d, Y'),
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
            $rawCoffeeData = [];
            $coffeeProductData = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $categories[] = $date->format('M d');
                
                // Calculate inventory levels for this specific date by looking at inventory updates
                $rawCoffeeTotal = $this->getInventoryLevelForDate($date, 'raw_coffee');
                $coffeeProductTotal = $this->getInventoryLevelForDate($date, 'coffee_product');
                
                $rawCoffeeData[] = $rawCoffeeTotal;
                $coffeeProductData[] = $coffeeProductTotal;
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
     * Calculate inventory level for a specific date based on inventory updates
     */
    private function getInventoryLevelForDate($date, $type): int
    {
        try {
            // Get current total
            if ($type === 'raw_coffee') {
                $currentTotal = Inventory::whereNotNull('raw_coffee_id')->sum('quantity_in_stock');
                $inventoryIds = Inventory::whereNotNull('raw_coffee_id')->pluck('id');
            } else {
                $currentTotal = Inventory::whereNotNull('coffee_product_id')->sum('quantity_in_stock');
                $inventoryIds = Inventory::whereNotNull('coffee_product_id')->pluck('id');
            }

            // Get all updates after the target date
            $updatesAfterDate = \App\Models\InventoryUpdate::whereIn('inventory_id', $inventoryIds)
                ->where('created_at', '>', $date->endOfDay())
                ->sum('quantity_change');

            // Subtract the changes that happened after the target date to get historical level
            $historicalLevel = $currentTotal - $updatesAfterDate;
            
            return max(0, $historicalLevel);
            
        } catch (\Exception $e) {
            // Fallback to current total if calculation fails
            if ($type === 'raw_coffee') {
                return Inventory::whereNotNull('raw_coffee_id')->sum('quantity_in_stock') ?? 0;
            } else {
                return Inventory::whereNotNull('coffee_product_id')->sum('quantity_in_stock') ?? 0;
            }
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
                // First check if it already contains names (not user IDs)
                if (!preg_match('/^[U]\d{5}/', $recipients) && !preg_match('/^\d+$/', $recipients)) {
                    // If it doesn't look like user IDs, it's probably already names
                    return $recipients;
                }
                
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

            // Check if the first element looks like a name rather than an ID
            $firstElement = $recipientIds[0];
            if (!preg_match('/^[U]\d{5}$/', $firstElement) && !is_numeric($firstElement)) {
                // These are already names, not IDs
                return implode(', ', $recipientIds);
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
                    // If user not found, show just the name or a generic label
                    if (is_string($id) && !preg_match('/^[U]\d{5}$/', $id)) {
                        $names[] = $id; // It's already a name
                    } else {
                        $names[] = "Unknown User";
                    }
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
            try {
                $categories[] = \Carbon\Carbon::parse((string)$row->demand_date)->format('M d');
            } catch (\Exception $e) {
                $categories[] = \Carbon\Carbon::now()->format('M d');
            }
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

    /**
     * Get supplier recent fulfilled orders from database
     */
    private function getSupplierRecentOrders(): array
    {
        try {
            $user = Auth::user();
            $supplierId = $user->supplier ? $user->supplier->id : null;
            
            if (!$supplierId) {
                // If no supplier found, return empty array instead of fallback
                return [];
            }

            // Get recent orders for this supplier (all statuses, not just fulfilled)
            $orders = Order::with(['supplier', 'rawCoffee'])
                ->where('supplier_id', $supplierId)
                ->orderBy('created_at', 'desc')
                ->limit(5) // Show 5 most recent orders
                ->get();

            $ordersData = [];
            
            foreach ($orders as $order) {
                // Format quantity with unit
                $quantity = number_format((float)$order->quantity, 0) . ' kg';
                
                // Format status for display with better styling
                $status = ucfirst($order->status);
                
                // Better customer identification
                $customer = 'Factory Order';
                
                $ordersData[] = [
                    'Order ID' => $order->id,
                    'Customer' => $customer,
                    'Product' => $order->rawCoffee ? $order->rawCoffee->coffee_type : 'Raw Coffee',
                    'Quantity (kg)' => $quantity,
                    'Status' => $status,
                    'Date' => $order->created_at->format('M d, Y'),
                ];
            }

            return $ordersData;

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error fetching supplier recent orders: ' . $e->getMessage());
            
            // Return empty array instead of fallback data
            return [];
        }
    }

    /**
     * Get admin dashboard statistics
     */
    private function getAdminStats(): array
    {
        try {
            // Active Orders (confirmed, shipped, but not delivered or cancelled)
            $activeOrders = Order::whereIn('status', ['confirmed', 'shipped'])->count();
            
            // Total Inventory Weight across all supply centers
            $totalInventoryWeight = Inventory::whereNotNull('supply_center_id')
                ->sum('quantity_in_stock');
            
            // Pending Shipments (confirmed orders that haven't been shipped yet)
            $pendingShipments = Order::where('status', 'confirmed')->count();
            
            // Calculate average quality score based on recent orders/coffee grades
            $qualityScore = $this->calculateQualityScore();
            
            // Calculate previous period stats for percentage changes
            $previousPeriodStats = $this->getPreviousPeriodStats('admin');
            
            return [
                'activeOrders' => [
                    'value' => $activeOrders,
                    'change' => $this->calculatePercentageChange($activeOrders, $previousPeriodStats['activeOrders']),
                ],
                'totalInventory' => [
                    'value' => number_format($totalInventoryWeight),
                    'change' => $this->calculatePercentageChange($totalInventoryWeight, $previousPeriodStats['totalInventory']),
                ],
                'pendingShipments' => [
                    'value' => $pendingShipments,
                    'change' => $this->calculatePercentageChange($pendingShipments, $previousPeriodStats['pendingShipments']),
                ],
                'qualityScore' => [
                    'value' => $qualityScore . '/100',
                    'change' => $this->calculatePercentageChange($qualityScore, $previousPeriodStats['qualityScore']),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating admin stats: ' . $e->getMessage());
            return $this->getDefaultAdminStats();
        }
    }

    /**
     * Get supplier dashboard statistics
     */
    private function getSupplierStats(): array
    {
        try {
            $user = Auth::user();
            $supplier = $user->supplier;
            
            if (!$supplier) {
                return $this->getDefaultSupplierStats();
            }
            
            // Active Orders received by this supplier
            $activeOrders = Order::where('supplier_id', $supplier->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            
            // Total inventory at supplier's supply center
            $totalInventoryWeight = 0;
            if ($supplier->warehouse_id) {
                $totalInventoryWeight = Inventory::where('warehouse_id', $supplier->warehouse_id)
                    ->sum('quantity_in_stock');
            }
            
            // Pending deliveries (confirmed orders)
            $pendingDeliveries = Order::where('supplier_id', $supplier->id)
                ->where('status', 'confirmed')
                ->count();
            
            // Calculate supplier quality score based on order completion rate
            $qualityScore = $this->calculateSupplierQualityScore($supplier->id);
            
            // Calculate previous period stats
            $previousPeriodStats = $this->getPreviousPeriodStats('supplier', $supplier->id);
            
            return [
                'activeOrders' => [
                    'value' => $activeOrders,
                    'change' => $this->calculatePercentageChange($activeOrders, $previousPeriodStats['activeOrders']),
                ],
                'totalInventory' => [
                    'value' => number_format($totalInventoryWeight),
                    'change' => $this->calculatePercentageChange($totalInventoryWeight, $previousPeriodStats['totalInventory']),
                ],
                'pendingDeliveries' => [
                    'value' => $pendingDeliveries,
                    'change' => $this->calculatePercentageChange($pendingDeliveries, $previousPeriodStats['pendingDeliveries']),
                ],
                'qualityScore' => [
                    'value' => $qualityScore . '/100',
                    'change' => $this->calculatePercentageChange($qualityScore, $previousPeriodStats['qualityScore']),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating supplier stats: ' . $e->getMessage());
            return $this->getDefaultSupplierStats();
        }
    }

    /**
     * Get vendor dashboard statistics
     */
    private function getVendorStats(): array
    {
        try {
            $user = Auth::user();
            $wholesaler = $user->wholesaler;
            
            if (!$wholesaler) {
                return $this->getDefaultVendorStats();
            }
            
            // Active Orders placed by this vendor
            $activeOrders = Order::where('wholesaler_id', $wholesaler->id)
                ->whereIn('status', ['pending', 'confirmed', 'shipped'])
                ->count();
            
            // Total inventory in vendor's warehouses
            $totalInventoryWeight = Inventory::whereHas('warehouse', function($query) use ($wholesaler) {
                $query->where('wholesaler_id', $wholesaler->id);
            })->sum('quantity_in_stock');
            
            // Orders in transit (shipped but not delivered)
            $ordersInTransit = Order::where('wholesaler_id', $wholesaler->id)
                ->where('status', 'shipped')
                ->count();
            
            // Number of warehouses
            $warehouseCount = $wholesaler->warehouses()->count();
            
            // Calculate previous period stats
            $previousPeriodStats = $this->getPreviousPeriodStats('vendor', $wholesaler->id);
            
            return [
                'activeOrders' => [
                    'value' => $activeOrders,
                    'change' => $this->calculatePercentageChange($activeOrders, $previousPeriodStats['activeOrders']),
                ],
                'totalInventory' => [
                    'value' => number_format($totalInventoryWeight),
                    'change' => $this->calculatePercentageChange($totalInventoryWeight, $previousPeriodStats['totalInventory']),
                ],
                'ordersInTransit' => [
                    'value' => $ordersInTransit,
                    'change' => $this->calculatePercentageChange($ordersInTransit, $previousPeriodStats['ordersInTransit']),
                ],
                'warehouseCount' => [
                    'value' => $warehouseCount,
                    'change' => $this->calculatePercentageChange($warehouseCount, $previousPeriodStats['warehouseCount']),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating vendor stats: ' . $e->getMessage());
            return $this->getDefaultVendorStats();
        }
    }

    /**
     * Calculate quality score based on order completion and defect rates
     */
    private function calculateQualityScore(): int
    {
        try {
            $totalOrders = Order::where('created_at', '>=', now()->subMonth())->count();
            if ($totalOrders === 0) return 95; // Default high score if no recent orders
            
            $completedOrders = Order::where('status', 'delivered')
                ->where('created_at', '>=', now()->subMonth())
                ->count();
            
            $completionRate = ($completedOrders / $totalOrders) * 100;
            
            // Base quality score on completion rate, capped between 70-100
            return min(100, max(70, intval($completionRate)));
        } catch (\Exception $e) {
            return 95; // Default fallback
        }
    }

    /**
     * Calculate supplier quality score based on order fulfillment
     */
    private function calculateSupplierQualityScore(string $supplierId): int
    {
        try {
            $totalOrders = Order::where('supplier_id', $supplierId)
                ->where('created_at', '>=', now()->subMonth())
                ->count();
            
            if ($totalOrders === 0) return 95;
            
            $fulfilledOrders = Order::where('supplier_id', $supplierId)
                ->whereIn('status', ['shipped', 'delivered'])
                ->where('created_at', '>=', now()->subMonth())
                ->count();
            
            $fulfillmentRate = ($fulfilledOrders / $totalOrders) * 100;
            
            return min(100, max(70, intval($fulfillmentRate)));
        } catch (\Exception $e) {
            return 95;
        }
    }

    /**
     * Get statistics from previous period for comparison
     */
    private function getPreviousPeriodStats(string $role, ?string $entityId = null): array
    {
        try {
            $previousWeekStart = now()->subWeeks(2)->startOfWeek();
            $previousWeekEnd = now()->subWeek()->endOfWeek();
            
            if ($role === 'admin') {
                return [
                    'activeOrders' => Order::whereIn('status', ['confirmed', 'shipped'])
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'totalInventory' => Inventory::whereNotNull('supply_center_id')
                        ->whereBetween('last_updated', [$previousWeekStart, $previousWeekEnd])
                        ->sum('quantity_in_stock'),
                    'pendingShipments' => Order::where('status', 'confirmed')
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'qualityScore' => 90, // Simplified for now
                ];
            } elseif ($role === 'supplier' && $entityId) {
                return [
                    'activeOrders' => Order::where('supplier_id', $entityId)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'totalInventory' => 0, // Simplified
                    'pendingDeliveries' => Order::where('supplier_id', $entityId)
                        ->where('status', 'confirmed')
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'qualityScore' => 90,
                ];
            } elseif ($role === 'vendor' && $entityId) {
                return [
                    'activeOrders' => Order::where('wholesaler_id', $entityId)
                        ->whereIn('status', ['pending', 'confirmed', 'shipped'])
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'totalInventory' => 0, // Simplified
                    'ordersInTransit' => Order::where('wholesaler_id', $entityId)
                        ->where('status', 'shipped')
                        ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
                        ->count(),
                    'warehouseCount' => 0, // Warehouses don't change frequently
                ];
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate percentage change between current and previous values
     */
    private function calculatePercentageChange($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'right',
                'type' => $current > 0 ? 'positive' : 'neutral'
            ];
        }
        
        $change = (($current - $previous) / $previous) * 100;
        
        return [
            'percentage' => abs(round($change, 1)),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'right'),
            'type' => $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral')
        ];
    }

    /**
     * Default fallback stats for admin
     */
    private function getDefaultAdminStats(): array
    {
        return [
            'activeOrders' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'totalInventory' => ['value' => '0', 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'pendingShipments' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'qualityScore' => ['value' => '95/100', 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
        ];
    }

    /**
     * Default fallback stats for supplier
     */
    private function getDefaultSupplierStats(): array
    {
        return [
            'activeOrders' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'totalInventory' => ['value' => '0', 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'pendingDeliveries' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'qualityScore' => ['value' => '95/100', 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
        ];
    }

    /**
     * Default fallback stats for vendor
     */
    private function getDefaultVendorStats(): array
    {
        return [
            'activeOrders' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'totalInventory' => ['value' => '0', 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'ordersInTransit' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
            'warehouseCount' => ['value' => 0, 'change' => ['percentage' => 0, 'direction' => 'right', 'type' => 'neutral']],
        ];
    }

    /**
     * Get dynamic inventory items for supplier dashboard
     */
    private function getSupplierInventoryItems(): array
    {
        $user = Auth::user();
        $supplier = $user->supplier;
        
        if (!$supplier) {
            return [];
        }

        // Get all warehouses for this supplier
        $warehouses = $supplier->warehouses;
        
        if ($warehouses->isEmpty()) {
            return [];
        }

        // Get inventory items from supplier's warehouses
        $inventoryItems = Inventory::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->with(['rawCoffee', 'warehouse'])
            ->get();

        if ($inventoryItems->isEmpty()) {
            return [];
        }

        // Group by coffee type and grade, take random 4 items
        $groupedItems = $inventoryItems->groupBy(function($item) {
            if ($item->rawCoffee) {
                return $item->rawCoffee->coffee_type . ' - ' . ($item->rawCoffee->grade ?? 'Unknown');
            }
            return 'Unknown Product';
        });

        $result = [];
        $count = 0;
        
        foreach ($groupedItems->take(4) as $groupName => $items) {
            $totalQuantity = $items->sum('quantity_in_stock');
            $warehouseIds = $items->pluck('warehouse_id')->unique();
            
            // Calculate allocated space: warehouse capacity divided by number of inventory items in that warehouse
            $allocatedSpace = 0;
            foreach ($warehouseIds as $warehouseId) {
                $warehouse = $warehouses->where('id', $warehouseId)->first();
                if ($warehouse) {
                    $itemsInWarehouse = $inventoryItems->where('warehouse_id', $warehouseId)->count();
                    $allocatedSpace += $itemsInWarehouse > 0 ? ($warehouse->capacity / $itemsInWarehouse) : 0;
                }
            }

            // Determine status based on quantity vs allocated space ratio
            $ratio = $allocatedSpace > 0 ? ($totalQuantity / $allocatedSpace) * 100 : 0;
            $statusLabel = 'Healthy';
            if ($ratio < 20) {
                $statusLabel = 'Critical';
            } elseif ($ratio < 50) {
                $statusLabel = 'Low';
            }

            $result[] = [
                'name' => $groupName,
                'available' => $totalQuantity,
                'allocated' => round($allocatedSpace, 2),
                'statusLabel' => $statusLabel,
            ];
            
            $count++;
            if ($count >= 4) break;
        }

        return $result;
    }

    /**
     * Get dynamic inventory items for vendor dashboard
     */
    private function getVendorInventoryItems(): array
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler) {
            return [];
        }

        // Get all warehouses for this wholesaler
        $warehouses = $wholesaler->warehouses;
        
        if ($warehouses->isEmpty()) {
            return [];
        }

        // Get inventory items from vendor's warehouses
        $inventoryItems = Inventory::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->with(['coffeeProduct', 'warehouse'])
            ->get();

        if ($inventoryItems->isEmpty()) {
            return [];
        }

        // Group by coffee product name, take random 4 items
        $groupedItems = $inventoryItems->groupBy(function($item) {
            if ($item->coffeeProduct) {
                return $item->coffeeProduct->name;
            }
            return 'Unknown Product';
        });

        $result = [];
        $count = 0;
        
        foreach ($groupedItems->take(4) as $groupName => $items) {
            $totalQuantity = $items->sum('quantity_in_stock');
            $warehouseIds = $items->pluck('warehouse_id')->unique();
            
            // Calculate allocated space: warehouse capacity divided by number of inventory items in that warehouse
            $allocatedSpace = 0;
            foreach ($warehouseIds as $warehouseId) {
                $warehouse = $warehouses->where('id', $warehouseId)->first();
                if ($warehouse) {
                    $itemsInWarehouse = $inventoryItems->where('warehouse_id', $warehouseId)->count();
                    $allocatedSpace += $itemsInWarehouse > 0 ? ($warehouse->capacity / $itemsInWarehouse) : 0;
                }
            }

            // Determine status based on quantity vs allocated space ratio
            $ratio = $allocatedSpace > 0 ? ($totalQuantity / $allocatedSpace) * 100 : 0;
            $statusLabel = 'Healthy';
            if ($ratio < 20) {
                $statusLabel = 'Critical';
            } elseif ($ratio < 50) {
                $statusLabel = 'Low';
            }

            $result[] = [
                'name' => $groupName,
                'available' => $totalQuantity,
                'allocated' => round($allocatedSpace, 2),
                'statusLabel' => $statusLabel,
            ];
            
            $count++;
            if ($count >= 4) break;
        }

        return $result;
    }

    /**
     * Get dynamic inventory items for admin dashboard
     */
    private function getAdminInventoryItems(): array
    {
        // Get inventory items from supply centers (admin manages supply centers)
        $inventoryItems = Inventory::whereNotNull('supply_center_id')
            ->with(['rawCoffee', 'coffeeProduct', 'supplyCenter'])
            ->get();

        if ($inventoryItems->isEmpty()) {
            return [];
        }

        // Group by product type, take random 4 items
        $groupedItems = $inventoryItems->groupBy(function($item) {
            if ($item->rawCoffee) {
                return $item->rawCoffee->coffee_type . ' - ' . ($item->rawCoffee->grade ?? 'Unknown');
            } elseif ($item->coffeeProduct) {
                return $item->coffeeProduct->name;
            }
            return 'Unknown Product';
        });

        $result = [];
        $count = 0;
        
        foreach ($groupedItems->take(4) as $groupName => $items) {
            $totalQuantity = $items->sum('quantity_in_stock');
            $supplyCenterIds = $items->pluck('supply_center_id')->unique()->filter();
            
            // Calculate allocated space: supply center capacity divided by number of inventory items
            $allocatedSpace = 0;
            foreach ($supplyCenterIds as $supplyCenterId) {
                $supplyCenter = \App\Models\SupplyCenter::find($supplyCenterId);
                if ($supplyCenter) {
                    $itemsInSupplyCenter = $inventoryItems->where('supply_center_id', $supplyCenterId)->count();
                    $allocatedSpace += $itemsInSupplyCenter > 0 ? ($supplyCenter->capacity / $itemsInSupplyCenter) : 0;
                }
            }

            // Determine status based on quantity vs allocated space ratio
            $ratio = $allocatedSpace > 0 ? ($totalQuantity / $allocatedSpace) * 100 : 0;
            $statusLabel = 'Healthy';
            if ($ratio < 20) {
                $statusLabel = 'Critical';
            } elseif ($ratio < 50) {
                $statusLabel = 'Low';
            }

            $result[] = [
                'name' => $groupName,
                'available' => $totalQuantity,
                'allocated' => round($allocatedSpace, 2),
                'statusLabel' => $statusLabel,
            ];
            
            $count++;
            if ($count >= 4) break;
        }

        return $result;
    }

}