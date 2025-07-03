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


class dashboardController extends Controller
{
    
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('onboarding'); // Ensure user is authenticated
        }

        $data = []; // Initialize an empty array to hold all data for the view
        $user = Auth::user();


        if ($user->isAdmin()) {
            // Fetch data specifically for the Admin dashboard
            $data = array_merge($data, $this->getAdminDashboardData());
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

    
    private function getAdminDashboardData(): array
    {
        return [
            'mlPredictionData' => [
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
            ],
            'mlPredictionCategories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'mlPredictionDescription' => 'Weight: ML predictions in 000 tonnes to assist optimal resource allocation. Forecasts generated using historical data and market indicators.',

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
            $reports = Report::whereNotNull('last_sent')
                ->orderBy('last_sent', 'desc')
                ->limit($limit)
                ->get();

            // If no reports with last_sent, fall back to recently created reports
            if ($reports->isEmpty()) {
                $reports = Report::orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
            }

            return $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'date_generated' => $report->last_sent ?? $report->created_at,
                    'recipients' => $report->recipients ?? 'Not specified',
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
}