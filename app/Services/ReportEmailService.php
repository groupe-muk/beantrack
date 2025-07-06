<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\InventoryUpdate;
use App\Models\CoffeeProduct;
use App\Models\RawCoffee;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Mail\ReportDelivered;
use App\Mail\AdHocReportGenerated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Exception;

class ReportEmailService
{
    /**
     * Send a scheduled report to recipients
     */
    public function sendScheduledReport(Report $report): bool
    {
        try {
            Log::info('Starting scheduled report email delivery', [
                'report_id' => $report->id,
                'report_name' => $report->name
            ]);

            // Generate the report file
            $filePath = $this->generateReportFile($report);
            
            if (!$filePath) {
                Log::error('Failed to generate report file for scheduled report', [
                    'report_id' => $report->id
                ]);
                return false;
            }
            
            Log::info('Report file generated for scheduled report', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0
            ]);
            
            // Get recipients
            $recipients = $this->getReportRecipients($report);
            
            if (empty($recipients)) {
                Log::warning('No recipients found for report', ['report_id' => $report->id]);
                return false;
            }

            // Send email to each recipient
            $emailsSent = 0;
            foreach ($recipients as $recipient) {
                try {
                    Log::info('Sending scheduled report email', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email,
                        'attachment_path' => $filePath,
                        'attachment_exists' => file_exists($filePath)
                    ]);
                    
                    Mail::to($recipient->email)->send(new ReportDelivered($report, $recipient, $filePath));
                    $emailsSent++;
                    Log::info('Report email sent successfully', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to send report email to recipient', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update report last_sent time but keep status as active for scheduled reports
            $report->update([
                'last_sent' => now(),
                'status' => 'active'  // Keep the report active so it continues to be scheduled
            ]);

            Log::info('Scheduled report delivery completed', [
                'report_id' => $report->id,
                'emails_sent' => $emailsSent,
                'total_recipients' => count($recipients)
            ]);

            // Clean up temporary file - DO THIS AFTER EMAIL IS SENT
            if ($filePath && file_exists($filePath)) {
                $deleteSuccess = unlink($filePath);
                Log::info('Temporary file cleanup', [
                    'report_id' => $report->id,
                    'file_path' => $filePath,
                    'delete_success' => $deleteSuccess
                ]);
            }

            return $emailsSent > 0;

        } catch (Exception $e) {
            Log::error('Failed to send scheduled report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send an ad-hoc report to recipients
     */
    public function sendAdHocReport(Report $report): bool
    {
        try {
            Log::info('Starting ad-hoc report email delivery', [
                'report_id' => $report->id,
                'report_name' => $report->name
            ]);

            // Generate the report file
            $filePath = $this->generateReportFile($report);
            
            if (!$filePath) {
                Log::error('Failed to generate report file for ad-hoc report', [
                    'report_id' => $report->id
                ]);
                return false;
            }
            
            Log::info('Report file generated for ad-hoc report', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0
            ]);
            
            // Get recipients (for ad-hoc reports, usually just the creator)
            $recipients = $this->getReportRecipients($report);
            
            if (empty($recipients)) {
                Log::warning('No recipients found for ad-hoc report', ['report_id' => $report->id]);
                return false;
            }

            // Send email to each recipient
            $emailsSent = 0;
            foreach ($recipients as $recipient) {
                try {
                    Log::info('Sending ad-hoc report email', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email,
                        'attachment_path' => $filePath,
                        'attachment_exists' => file_exists($filePath)
                    ]);
                    
                    Mail::to($recipient->email)->send(new AdHocReportGenerated($report, $recipient, $filePath));
                    $emailsSent++;
                    Log::info('Ad-hoc report email sent successfully', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to send ad-hoc report email to recipient', [
                        'report_id' => $report->id,
                        'recipient' => $recipient->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update report status
            $report->update([
                'last_sent' => now(),
                'status' => 'completed'
            ]);

            Log::info('Ad-hoc report delivery completed', [
                'report_id' => $report->id,
                'emails_sent' => $emailsSent,
                'total_recipients' => count($recipients)
            ]);

            // Clean up temporary file - DO THIS AFTER EMAIL IS SENT
            if ($filePath && file_exists($filePath)) {
                $deleteSuccess = unlink($filePath);
                Log::info('Temporary file cleanup', [
                    'report_id' => $report->id,
                    'file_path' => $filePath,
                    'delete_success' => $deleteSuccess
                ]);
            }

            return $emailsSent > 0;

        } catch (Exception $e) {
            Log::error('Failed to send ad-hoc report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Generate the report file
     */
    private function generateReportFile(Report $report): ?string
    {
        try {
            // Create temporary file
            $tempDir = storage_path('app/temp/reports');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $fileName = $this->generateFileName($report);
            $filePath = $tempDir . '/' . $fileName;

            Log::info('Generating report file', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'format' => $report->format
            ]);

            // Generate content based on report type
            $data = $this->generateReportData($report);

            switch ($report->format) {
                case 'pdf':
                    $this->generatePdfReport($data, $filePath, $report);
                    break;
                case 'excel':
                    $this->generateExcelReport($data, $filePath, $report);
                    break;
                case 'csv':
                    $this->generateCsvReport($data, $filePath, $report);
                    break;
                default:
                    $this->generatePdfReport($data, $filePath, $report);
            }

            // Verify file was created successfully
            if (!file_exists($filePath)) {
                Log::error('Report file was not created', [
                    'report_id' => $report->id,
                    'file_path' => $filePath
                ]);
                return null;
            }

            $fileSize = filesize($filePath);
            Log::info('Report file generated successfully', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_size' => $fileSize
            ]);

            return $filePath;

        } catch (Exception $e) {
            Log::error('Failed to generate report file', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Generate report data based on report type
     */
    private function generateReportData(Report $report): array
    {
        // Ensure content is properly decoded from JSON
        $content = $report->content;
        if (is_string($content)) {
            $content = json_decode($content, true) ?? [];
        } elseif (!is_array($content)) {
            $content = [];
        }
        
        $reportType = $content['report_type'] ?? $report->type ?? 'general';
        
        // If we have a template, prioritize template mapping over stored report_type
        if (isset($content['template'])) {
            $mappedType = $this->mapTemplateToReportType($content['template']);
            Log::info('EmailService: Template found - using template mapping', [
                'template' => $content['template'],
                'stored_report_type' => $reportType,
                'mapped_type' => $mappedType
            ]);
            $reportType = $mappedType;
        }

        // Generate actual data from database
        return [
            'title' => $report->name,
            'type' => $reportType,
            'generated_at' => now(),
            'data' => $this->getActualDataForReportType($reportType, $content, $report),
            'summary' => $this->generateReportSummary($reportType, $content, $report),
            'filters' => $content['filters'] ?? [],
            'date_range' => [
                'from' => $content['from_date'] ?? null,
                'to' => $content['to_date'] ?? null
            ]
        ];
    }

    /**
     * Generate actual data for different report types
     */
    private function getActualDataForReportType(string $type, array $content, Report $report): array
    {
        // Ensure content is array - additional safety check
        if (!is_array($content)) {
            Log::warning('Content parameter is not an array in getActualDataForReportType', [
                'type' => gettype($content),
                'content' => $content
            ]);
            $content = [];
        }

        $fromDate = $content['from_date'] ?? now()->subDays(30)->format('Y-m-d');
        $toDate = $content['to_date'] ?? now()->format('Y-m-d');
        
        // Get the current user from report creator
        $userId = $report->created_by ?? (Auth::check() ? Auth::id() : null);
        $user = $userId ? User::find($userId) : null;

        // Fetch actual data from database based on report type with user filtering
        switch ($type) {
            case 'sales_data':
                return $this->getSalesData($fromDate, $toDate, $user);
            case 'inventory_movements':
                return $this->getInventoryMovements($fromDate, $toDate, $user);
            case 'order_history':
                return $this->getOrderHistory($fromDate, $toDate, $user);
            case 'production_batches':
                return $this->getProductionBatches($fromDate, $toDate, $user);
            case 'supplier_inventory':
                return $this->getSupplierInventory($fromDate, $toDate, $user);
            case 'supplier_orders':
                return $this->getSupplierOrders($fromDate, $toDate, $user);
            case 'vendor_purchases':
                return $this->getVendorPurchases($fromDate, $toDate, $user);
            case 'vendor_orders':
                return $this->getVendorOrders($fromDate, $toDate, $user);
            case 'vendor_deliveries':
                return $this->getVendorDeliveries($fromDate, $toDate, $user);
            case 'vendor_payments':
                return $this->getVendorPayments($fromDate, $toDate, $user);
            case 'vendor_inventory':
                return $this->getVendorInventory($fromDate, $toDate, $user);
            default:
                return $this->getGenericData($fromDate, $toDate, $user);
        }
    }

    /**
     * Get sales data from database
     */
    private function getSalesData(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->whereIn('status', ['completed', 'delivered']);

        // Filter by user permissions - use authenticated user if no user provided
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }
        
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see orders where they are the supplier
                $query->where('supplier_id', $user->id);
            } elseif ($user->role === 'vendor') {
                // Vendors can only see their own orders through wholesaler relationship
                $query->where('wholesaler_id', $user->wholesaler->id ?? null);
            }
            // Admins can see all orders (no additional filter)
        }

        $orders = $query->get();

        $data = [];
        foreach ($orders as $order) {
            $productName = $order->coffeeProduct ? $order->coffeeProduct->name : 
                          ($order->rawCoffee ? $order->rawCoffee->type : 'Unknown Product');
            
            $data[] = [
                'Order ID' => $order->id,
                'Date' => $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A',
                'Product' => $productName,
                'Quantity' => $order->quantity ?? 0,
                'Total Amount' => '$' . number_format($order->total_amount ?? 0, 2),
                'Customer' => $order->wholesaler ? $order->wholesaler->name : 'N/A'
            ];
        }

        return $data ?: [['Message' => 'No sales data found for the specified date range']];
    }

    /**
     * Get inventory movements from database
     */
    private function getInventoryMovements(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = InventoryUpdate::with(['inventory.coffeeProduct', 'inventory.rawCoffee', 'inventory.supplyCenter'])
            ->whereBetween('created_at', [$fromDate, $toDate]);

        // Filter by user permissions
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see inventory movements for their supply centers
                $query->whereHas('inventory.supplyCenter', function($q) use ($user) {
                    $q->where('supplier_id', $user->id);
                });
            }
            // Admins and wholesalers can see all movements (no additional filter for now)
        }

        $movements = $query->orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($movements as $movement) {
            $productName = $movement->inventory->coffeeProduct ? $movement->inventory->coffeeProduct->name : 
                          ($movement->inventory->rawCoffee ? $movement->inventory->rawCoffee->type : 'Unknown Product');
            
            $data[] = [
                'Date' => $movement->created_at->format('Y-m-d'),
                'Product' => $productName,
                'Movement Type' => $movement->update_type ?? 'Update',
                'Quantity Change' => $movement->quantity_change ?? 0,
                'New Stock Level' => $movement->new_quantity ?? 0,
                'Location' => $movement->inventory->supplyCenter ? $movement->inventory->supplyCenter->name : 'N/A'
            ];
        }

        return $data ?: [['Message' => 'No inventory movements found for the specified date range']];
    }

    /**
     * Get order history from database
     */
    private function getOrderHistory(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
            ->whereBetween('order_date', [$fromDate, $toDate]);

        // Filter by user permissions - use authenticated user if no user provided
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }
        
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see orders where they are the supplier
                $query->where('supplier_id', $user->id);
            } elseif ($user->role === 'vendor') {
                // Vendors can only see their own orders through wholesaler relationship
                $query->where('wholesaler_id', $user->wholesaler->id ?? null);
            }
            // Admins can see all orders (no additional filter)
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        $data = [];
        foreach ($orders as $order) {
            $productName = $order->coffeeProduct ? $order->coffeeProduct->name : 
                          ($order->rawCoffee ? $order->rawCoffee->type : 'Unknown Product');
            
            $data[] = [
                'Order ID' => $order->id,
                'Date' => $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A',
                'Product' => $productName,
                'Quantity' => $order->quantity ?? 0,
                'Status' => ucfirst($order->status ?? 'unknown'),
                'Total Amount' => '$' . number_format($order->total_amount ?? 0, 2),
                'Customer' => $order->wholesaler ? $order->wholesaler->name : 'N/A'
            ];
        }

        return $data ?: [['Message' => 'No orders found for the specified date range']];
    }

    /**
     * Get production batches from database
     */
    private function getProductionBatches(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = CoffeeProduct::with(['rawCoffee'])
            ->whereBetween('created_at', [$fromDate, $toDate]);

        // Filter by user permissions
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see products they produced (need to add supplier_id to coffee_products table)
                // For now, filter by related raw coffee supplier
                $query->whereHas('rawCoffee', function($q) use ($user) {
                    $q->where('supplier_id', $user->id);
                });
            }
            // Admins can see all production batches (no additional filter)
        }

        $products = $query->get();

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'Product ID' => $product->id,
                'Product Name' => $product->name,
                'Created Date' => $product->created_at->format('Y-m-d'),
                'Raw Coffee Type' => $product->rawCoffee ? $product->rawCoffee->type : 'N/A',
                'Price' => '$' . number_format($product->price ?? 0, 2),
                'Quality Grade' => $product->quality_grade ?? 'N/A'
            ];
        }

        return $data ?: [['Message' => 'No production batches found for the specified date range']];
    }

    /**
     * Get supplier inventory from database
     */
    private function getSupplierInventory(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = Inventory::with(['coffeeProduct', 'rawCoffee', 'supplyCenter'])
            ->whereBetween('last_updated', [$fromDate, $toDate]);

        // Filter by user permissions
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see inventory at their supply centers
                $query->whereHas('supplyCenter', function($q) use ($user) {
                    $q->where('supplier_id', $user->id);
                });
            }
            // Admins can see all inventory (no additional filter)
        }

        $inventory = $query->get();

        $data = [];
        foreach ($inventory as $item) {
            $productName = $item->coffeeProduct ? $item->coffeeProduct->name : 
                          ($item->rawCoffee ? $item->rawCoffee->type : 'Unknown Product');
            
            $data[] = [
                'Product' => $productName,
                'Current Stock' => $item->quantity_in_stock ?? 0,
                'Supply Center' => $item->supplyCenter ? $item->supplyCenter->name : 'N/A',
                'Last Updated' => $item->last_updated ? date('Y-m-d', strtotime($item->last_updated)) : 'N/A'
            ];
        }

        return $data ?: [['Message' => 'No inventory data found for the specified date range']];
    }

    /**
     * Get supplier orders from database
     */
    private function getSupplierOrders(string $fromDate, string $toDate, ?User $user = null): array
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->whereNotNull('supplier_id');

        // Filter by user permissions
        if ($user) {
            if ($user->role === 'supplier') {
                // Suppliers can only see orders where they are the supplier
                $query->where('supplier_id', $user->id);
            }
            // Admins can see all supplier orders (no additional filter)
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        $data = [];
        foreach ($orders as $order) {
            $productName = $order->coffeeProduct ? $order->coffeeProduct->name : 
                          ($order->rawCoffee ? $order->rawCoffee->type : 'Unknown Product');
            
            $data[] = [
                'Order ID' => $order->id,
                'Supplier' => $order->supplier ? $order->supplier->name : 'N/A',
                'Product' => $productName,
                'Quantity' => $order->quantity ?? 0,
                'Status' => ucfirst($order->status ?? 'unknown'),
                'Date' => $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A',
                'Total Amount' => '$' . number_format($order->total_amount ?? 0, 2)
            ];
        }

        return $data ?: [['Message' => 'No supplier orders found for the specified date range']];
    }

    /**
     * Get vendor purchases data
     */
    private function getVendorPurchases($fromDate, $toDate, $user)
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
                     ->whereBetween('created_at', [$fromDate, $toDate]);
        
        if ($user->role === 'vendor') {
            // Vendors can only see orders where they are the purchaser/vendor
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $orders = $query->get();
        
        $totalPurchases = $orders->count();
        $totalAmount = $orders->sum('total_price');
        $avgPurchase = $totalPurchases > 0 ? $totalAmount / $totalPurchases : 0;
        
        $data = [['Order ID', 'Product', 'Raw Coffee', 'Supplier', 'Quantity', 'Total Price', 'Status', 'Order Date']];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->coffeeProduct->name ?? 'N/A',
                $order->rawCoffee->name ?? 'N/A',
                $order->supplier->company_name ?? 'N/A',
                $order->quantity ?? 0,
                '$' . number_format($order->total_price ?? 0, 2),
                ucfirst($order->status),
                $order->created_at->format('Y-m-d')
            ];
        }
        
        return [
            'title' => 'Vendor Purchases Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_amount' => '$' . number_format($totalAmount, 2),
                'average_purchase' => '$' . number_format($avgPurchase, 2),
                'vendor_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Get vendor orders data
     */
    private function getVendorOrders($fromDate, $toDate, $user)
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
                     ->whereBetween('created_at', [$fromDate, $toDate]);
        
        if ($user->role === 'vendor') {
            // Vendors can only see orders where they are the purchaser/vendor
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $orders = $query->get();
        
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total_price');
        $pendingOrders = $orders->where('status', 'pending')->count();
        $completedOrders = $orders->where('status', 'completed')->count();
        
        $data = [['Order ID', 'Product', 'Raw Coffee', 'Supplier', 'Quantity', 'Status', 'Order Date', 'Total Price']];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->coffeeProduct->name ?? 'N/A',
                $order->rawCoffee->name ?? 'N/A',
                $order->supplier->company_name ?? 'N/A',
                $order->quantity ?? 0,
                ucfirst($order->status),
                $order->created_at->format('Y-m-d'),
                '$' . number_format($order->total_price ?? 0, 2)
            ];
        }
        
        return [
            'title' => 'Vendor Orders Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_orders' => $totalOrders,
                'total_amount' => '$' . number_format($totalAmount, 2),
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'vendor_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Get vendor deliveries data
     */
    private function getVendorDeliveries($fromDate, $toDate, $user)
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
                     ->whereBetween('created_at', [$fromDate, $toDate])
                     ->where('status', 'delivered'); // Filter by delivered status instead
        
        if ($user->role === 'vendor') {
            // Vendors can only see orders where they are the purchaser/vendor
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $orders = $query->get();
        
        $totalDeliveries = $orders->count();
        $totalAmount = $orders->sum('total_price');
        
        $data = [['Order ID', 'Product', 'Raw Coffee', 'Supplier', 'Quantity', 'Order Date', 'Status', 'Total Price']];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->coffeeProduct->name ?? 'N/A',
                $order->rawCoffee->name ?? 'N/A',
                $order->supplier->company_name ?? 'N/A',
                $order->quantity ?? 0,
                $order->created_at->format('Y-m-d'),
                ucfirst($order->status),
                '$' . number_format($order->total_price ?? 0, 2)
            ];
        }
        
        return [
            'title' => 'Vendor Deliveries Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_deliveries' => $totalDeliveries,
                'total_amount' => '$' . number_format($totalAmount, 2),
                'vendor_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Get vendor payments data
     */
    private function getVendorPayments($fromDate, $toDate, $user)
    {
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
                     ->whereBetween('created_at', [$fromDate, $toDate]);
        
        if ($user->role === 'vendor') {
            // Vendors can only see orders where they are the purchaser/vendor
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $orders = $query->get();
        
        $totalPayments = $orders->count();
        $totalAmount = $orders->sum('total_price');
        $avgPayment = $totalPayments > 0 ? $totalAmount / $totalPayments : 0;
        
        $data = [['Order ID', 'Product', 'Raw Coffee', 'Supplier', 'Total Price', 'Quantity', 'Status', 'Order Date']];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->coffeeProduct->name ?? 'N/A',
                $order->rawCoffee->name ?? 'N/A',
                $order->supplier->company_name ?? 'N/A',
                '$' . number_format($order->total_price ?? 0, 2),
                $order->quantity ?? 0,
                ucfirst($order->status),
                $order->created_at->format('Y-m-d')
            ];
        }
        
        return [
            'title' => 'Vendor Payments Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_payments' => $totalPayments,
                'total_amount' => '$' . number_format($totalAmount, 2),
                'average_payment' => '$' . number_format($avgPayment, 2),
                'vendor_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Get vendor inventory data
     */
    private function getVendorInventory($fromDate, $toDate, $user)
    {
        $query = Inventory::with(['coffeeProduct', 'rawCoffee', 'supplyCenter'])
                          ->whereBetween('created_at', [$fromDate, $toDate]);
        
        // Filter by user permissions - vendors can only see their own inventory
        if ($user && $user->role === 'vendor') {
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $inventory = $query->get();
        
        $totalItems = $inventory->count();
        $lowStockItems = $inventory->filter(function($item) {
            return $item->quantity_in_stock <= ($item->minimum_quantity ?? 0);
        })->count();
        
        $data = [['Product', 'Raw Coffee', 'Quantity in Stock', 'Supply Center', 'Location', 'Last Updated']];
        foreach ($inventory as $item) {
            $data[] = [
                $item->coffeeProduct->name ?? 'N/A',
                $item->rawCoffee->name ?? 'N/A',
                $item->quantity_in_stock ?? 0,
                $item->supplyCenter->name ?? 'N/A',
                $item->supplyCenter->location ?? 'N/A',
                $item->created_at->format('Y-m-d H:i')
            ];
        }
        
        return [
            'title' => 'Vendor Inventory Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_items' => $totalItems,
                'low_stock_items' => $lowStockItems,
                'vendor_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport(array $data, string $filePath, Report $report): void
    {
        try {
            Log::info('Generating PDF report', [
                'report_id' => $report->id,
                'file_path' => $filePath
            ]);

            $html = view('reports.pdf-template', compact('data', 'report'))->render();
            $pdf = Pdf::loadHTML($html);
            $pdf->save($filePath);

            Log::info('PDF report generated successfully', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate PDF report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport(array $data, string $filePath, Report $report): void
    {
        try {
            Log::info('Generating Excel report', [
                'report_id' => $report->id,
                'file_path' => $filePath
            ]);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set title
            $sheet->setCellValue('A1', $data['title']);
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

            // Set headers
            $row = 3;
            if (!empty($data['data'])) {
                $headers = array_keys($data['data'][0]);
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Add data
                $row++;
                foreach ($data['data'] as $dataRow) {
                    $col = 'A';
                    foreach ($dataRow as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    $row++;
                }
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            Log::info('Excel report generated successfully', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate Excel report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate CSV report
     */
    private function generateCsvReport(array $data, string $filePath, Report $report): void
    {
        try {
            Log::info('Generating CSV report', [
                'report_id' => $report->id,
                'file_path' => $filePath
            ]);

            $file = fopen($filePath, 'w');
            
            if (!$file) {
                throw new Exception('Could not open file for writing: ' . $filePath);
            }
            
            // Write title
            fputcsv($file, [$data['title']]);
            fputcsv($file, []); // Empty row
            
            // Write data
            if (!empty($data['data'])) {
                // Write headers
                fputcsv($file, array_keys($data['data'][0]));
                
                // Write data rows
                foreach ($data['data'] as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);

            Log::info('CSV report generated successfully', [
                'report_id' => $report->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath)
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate CSV report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get recipients for the report
     */
    private function getReportRecipients(Report $report): array
    {
        $recipients = [];

        // For ad-hoc reports, send to creator
        if ($report->type === 'adhoc') {
            if ($report->creator) {
                $recipients[] = $report->creator;
            }
        } else {
            // For scheduled reports, get recipients from the report configuration
            if ($report->recipient) {
                $recipients[] = $report->recipient;
            }
            
            // Also send to the creator if it's a different person
            if ($report->creator && $report->creator->id !== $report->recipient_id) {
                $recipients[] = $report->creator;
            }
        }

        return $recipients;
    }

    /**
     * Generate file name for the report
     */
    private function generateFileName(Report $report): string
    {
        $name = str_replace(' ', '_', $report->name);
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);
        $extension = $report->format === 'excel' ? 'xlsx' : ($report->format === 'csv' ? 'csv' : 'pdf');
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "{$name}_{$timestamp}.{$extension}";
    }

    /**
     * Generate report summary
     */
    private function generateReportSummary(string $type, array $content = [], ?Report $report = null): string
    {
        $fromDate = $content['from_date'] ?? now()->subDays(30)->format('Y-m-d');
        $toDate = $content['to_date'] ?? now()->format('Y-m-d');
        
        // Get user for filtering summary data
        $userId = $report ? $report->created_by : (Auth::check() ? Auth::id() : null);
        $user = $userId ? User::find($userId) : null;
        
        switch ($type) {
            case 'sales_data':
                $query = Order::whereBetween('order_date', [$fromDate, $toDate])
                    ->whereIn('status', ['completed', 'delivered']);
                    
                // Apply user filtering for summary too
                if ($user && $user->role === 'supplier') {
                    $query->where('supplier_id', $user->id);
                } elseif ($user && $user->role === 'vendor') {
                    $query->where('wholesaler_id', $user->wholesaler->id ?? null);
                }
                
                $totalOrders = $query->count();
                $totalRevenue = $query->sum('total_amount');
                return "Total completed orders: {$totalOrders}, Total revenue: $" . number_format($totalRevenue, 2);
                
            case 'inventory_movements':
                $query = InventoryUpdate::whereBetween('created_at', [$fromDate, $toDate]);
                
                // Apply user filtering
                if ($user && $user->role === 'supplier') {
                    $query->whereHas('inventory.supplyCenter', function($q) use ($user) {
                        $q->where('supplier_id', $user->id);
                    });
                }
                
                $movementCount = $query->count();
                return "Total inventory movements: {$movementCount} for the period {$fromDate} to {$toDate}";
                
            case 'supplier_inventory':
                $query = Inventory::whereBetween('last_updated', [$fromDate, $toDate]);
                
                // Apply user filtering
                if ($user && $user->role === 'supplier') {
                    $query->whereHas('supplyCenter', function($q) use ($user) {
                        $q->where('supplier_id', $user->id);
                    });
                }
                
                $inventoryItems = $query->count();
                return "Total inventory items tracked: {$inventoryItems} for the period {$fromDate} to {$toDate}";
                
            case 'supplier_orders':
                $query = Order::whereBetween('order_date', [$fromDate, $toDate])
                    ->whereNotNull('supplier_id');
                    
                // Apply user filtering
                if ($user && $user->role === 'supplier') {
                    $query->where('supplier_id', $user->id);
                }
                
                $supplierOrders = $query->count();
                return "Total supplier orders: {$supplierOrders} for the period {$fromDate} to {$toDate}";
                
            case 'order_history':
                $query = Order::whereBetween('order_date', [$fromDate, $toDate]);
                
                // Apply user filtering
                if ($user && $user->role === 'supplier') {
                    $query->where('supplier_id', $user->id);
                } elseif ($user && $user->role === 'vendor') {
                    $query->where('wholesaler_id', $user->wholesaler->id ?? null);
                }
                
                $allOrders = $query->count();
                return "Total orders processed: {$allOrders} for the period {$fromDate} to {$toDate}";
                
            case 'production_batches':
                $query = CoffeeProduct::whereBetween('created_at', [$fromDate, $toDate]);
                
                // Apply user filtering
                if ($user && $user->role === 'supplier') {
                    $query->whereHas('rawCoffee', function($q) use ($user) {
                        $q->where('supplier_id', $user->id);
                    });
                }
                
                $productCount = $query->count();
                return "Total products created: {$productCount} for the period {$fromDate} to {$toDate}";
                
            default:
                return "Report generated for the period {$fromDate} to {$toDate}";
        }
    }

    /**
     * Get generic data for undefined report types
     */
    private function getGenericData($fromDate, $toDate, $user)
    {
        // Return basic order data as fallback
        $query = Order::with(['supplier', 'wholesaler', 'coffeeProduct', 'rawCoffee'])
                     ->whereBetween('created_at', [$fromDate, $toDate]);
        
        // Apply user-specific filtering
        if ($user && $user->role === 'supplier') {
            $query->where('supplier_id', $user->id);
        } elseif ($user && $user->role === 'vendor') {
            $query->where('wholesaler_id', $user->wholesaler->id ?? null);
        }
        
        $orders = $query->get();
        
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total_price');
        
        $data = [['Order ID', 'Product', 'Raw Coffee', 'Supplier', 'Quantity', 'Status', 'Order Date']];
        foreach ($orders as $order) {
            $data[] = [
                $order->id,
                $order->coffeeProduct->name ?? 'N/A',
                $order->rawCoffee->name ?? 'N/A',
                $order->supplier->company_name ?? 'N/A',
                $order->quantity ?? 0,
                ucfirst($order->status),
                $order->created_at->format('Y-m-d')
            ];
        }
        
        return [
            'title' => 'Generic Report',
            'period' => $fromDate . ' to ' . $toDate,
            'summary' => [
                'total_orders' => $totalOrders,
                'total_amount' => '$' . number_format($totalAmount, 2),
                'user_name' => $user ? $user->name : 'N/A'
            ],
            'data' => $data
        ];
    }

    /**
     * Debug method to check file system issues
     */
    public function debugFileSystem(Report $report): array
    {
        $debug = [];
        
        // Check storage path
        $storagePath = storage_path('app/temp/reports');
        $debug['storage_path'] = $storagePath;
        $debug['storage_exists'] = is_dir($storagePath);
        $debug['storage_writable'] = is_writable($storagePath);
        
        // Check if we can create the directory
        if (!$debug['storage_exists']) {
            $debug['mkdir_success'] = mkdir($storagePath, 0755, true);
            $debug['storage_exists_after_mkdir'] = is_dir($storagePath);
            $debug['storage_writable_after_mkdir'] = is_writable($storagePath);
        }
        
        // Generate filename
        $fileName = $this->generateFileName($report);
        $filePath = $storagePath . '/' . $fileName;
        $debug['file_name'] = $fileName;
        $debug['file_path'] = $filePath;
        
        // Test file creation
        $testContent = "Test content for debugging";
        $debug['test_file_write'] = file_put_contents($filePath, $testContent) !== false;
        $debug['test_file_exists'] = file_exists($filePath);
        $debug['test_file_size'] = file_exists($filePath) ? filesize($filePath) : 0;
        $debug['test_file_readable'] = is_readable($filePath);
        
        // Clean up test file
        if (file_exists($filePath)) {
            $debug['test_file_cleanup'] = unlink($filePath);
        }
        
        // Check PDF template
        $debug['pdf_template_exists'] = view()->exists('reports.pdf-template');
        
        // Check required classes
        $debug['dompdf_available'] = class_exists('Barryvdh\DomPDF\Facade\Pdf');
        $debug['spreadsheet_available'] = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        
        return $debug;
    }

    /**
     * Map template names to report types
     */
    private function mapTemplateToReportType($template)
    {
        $reportTypeMapping = [
            // Admin/General templates
            'Monthly Supplier Demand' => 'sales_data',
            'Monthly Supplier Demand Forecast' => 'sales_data',
            'Weekly Production Efficiency' => 'production_batches',
            'Daily Retail Sales Summary' => 'sales_data',
            'Daily Sales Summary' => 'sales_data',  // Fix for the current report
            'Quarterly Quality Control Report' => 'quality_metrics',
            'Quality Control Report' => 'quality_metrics',
            'Quality Report' => 'quality_metrics',
            'Quality Metrics Report' => 'quality_metrics',
            'Quality Control Analysis' => 'quality_metrics',
            'Inventory Movement Analysis' => 'inventory_movements',
            // Vendor templates
            'Vendor Purchases Report' => 'vendor_purchases',
            'Vendor Orders Report' => 'vendor_orders',
            'Vendor Deliveries Report' => 'vendor_deliveries',
            'Vendor Payments Report' => 'vendor_payments',
            'Vendor Inventory Report' => 'vendor_inventory',
            // Supplier templates
            'Supplier Inventory Report' => 'supplier_inventory',
            'Supplier Orders Report' => 'supplier_orders',
            'Supplier Quality Report' => 'quality_metrics',
            'Supplier Delivery Report' => 'supplier_orders',
            'Supplier Performance Report' => 'supplier_performance'
        ];

        return $reportTypeMapping[$template] ?? 'inventory_movements';
    }
}
