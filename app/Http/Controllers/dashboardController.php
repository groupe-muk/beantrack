<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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

            'productsTableHeaders' => ['Product Name', 'Price (UGX)', 'Stock', 'Status'],
            'productsTableData' => [
            ['Product Name' => 'Ugandan Coffee Beans', 'Price (UGX)' => '25,000', 'Stock' => 150, 'Status' => 'In Stock'],
            ['Product Name' => 'Organic Tea Leaves', 'Price (UGX)' => '12,500', 'Stock' => 0, 'Status' => 'Out of Stock'],
            ['Product Name' => 'Local Honey', 'Price (UGX)' => '18,000', 'Stock' => 75, 'Status' => 'Limited Stock'],
            ['Product Name' => 'Dried Fruits Mix', 'Price (UGX)' => '30,000', 'Stock' => 200, 'Status' => 'In Stock'],
            ],

            'lineChartData' => [
            [
                'name' => 'Arabica',
                'data' => [50, 55, 60, 58, 65, 70]
            ],
            [
                'name' => 'Robusta',
                'data' => [20, 25, 28, 35, 30, 45]
            ],
                
            [
                'name' => 'Excella',
                'data' => [30, 40, 35, 50, 49, 60]
            ]      
            ],
            
            'lineChartCategories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],

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
            ],
            'inventoryData' => [120, 150, 130, 170, 160, 190, 200],
            'inventoryData2' => [80, 100, 90, 110, 105, 120, 130],
            'inventoryCategories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],

        ];   

    }

 
    private function getSupplierDashboardData(): array
    {
        // Example:
        // return [
        //     'supplierOrders' => Order::where('supplier_id', Auth::id())->get(),
        //     'supplierMetrics' => someSupplierService::getMetrics(),
        // ];
        return []; // Return an empty array if no specific data yet
    }


    private function getVendorDashboardData(): array
    {
        // Example:
        // return [
        //     'vendorSales' => Sale::where('vendor_id', Auth::id())->get(),
        // ];
        return []; // Return an empty array if no specific data yet
    }
}