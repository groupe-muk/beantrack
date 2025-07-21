<div class="bg-light-background min-h-screen dark:bg-dark-background">
    <p class="text-soft-brown pb-5">Track and manage your coffee supply chain efficiently</p>
    <div class="w-full flex flex-col space-y-8">

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <x-stats-card
                    title="Active Orders"
                    value="{{ $adminStats['activeOrders']['value'] }}"
                    iconClass="fa-cube"
                    changeText="{{ $adminStats['activeOrders']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $adminStats['activeOrders']['change']['direction'] }}"
                    changeType="{{ $adminStats['activeOrders']['change']['type'] }}"
                />
                <x-stats-card
                    title="Total Inventory"
                    value="{{ $adminStats['totalInventory']['value'] }}"
                    iconClass="fa-weight-hanging"
                    unit="kg"
                    changeText="{{ $adminStats['totalInventory']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $adminStats['totalInventory']['change']['direction'] }}"
                    changeType="{{ $adminStats['totalInventory']['change']['type'] }}"
                />
                <x-stats-card
                    title="Pending Shipments"
                    value="{{ $adminStats['pendingShipments']['value'] }}"
                    iconClass="fa-calendar-alt"
                    unit="shipments"
                    changeText="{{ $adminStats['pendingShipments']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $adminStats['pendingShipments']['change']['direction'] }}"
                    changeType="{{ $adminStats['pendingShipments']['change']['type'] }}"
                />
                <x-stats-card
                    title="Quality Score"
                    value="{{ $adminStats['qualityScore']['value'] }}"
                    iconClass="fa-star"
                    unit=""
                    changeText="{{ $adminStats['qualityScore']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $adminStats['qualityScore']['change']['direction'] }}"
                    changeType="{{ $adminStats['qualityScore']['change']['type'] }}"
                />
            </div>
        </div>

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
            <x-orders-card
                title="Pending Orders"
                :orders="$pendingOrders"
                class="min-h-[200px]" 
            />
            

            
            <x-column-graph-card
                    title="Inventory Levels"
                    column-chart-i-d="dailySalesChart"
                    :chart-data="$inventoryData"
                    :chart-data2="$inventoryData2"
                    :chart-categories="$inventoryCategories"
                    seriesName="Raw Coffee Stock (kg)"
                    seriesName2="Coffee Products Stock (kg)"
                />


            </div>
        </div>

        <div class="space-y-6">

            <x-ml-prediction-graph-card
            title="Demand Forecast Analytics"
            chart-title="Coffee Demand Predictions & Historical Trends"
            prediction-chart-i-d="mlPredictionsChart"
            :chart-data="$mlPredictionData"
            :chart-categories="$mlPredictionCategories"
            :description="$mlPredictionDescription"
            :products="$products ?? collect()"
            :current-product-id="$currentProductId ?? null"
            class="h-[600px]" {{-- Adjust height as needed for visual balance --}}
            />

        </div>

         <div class="grid grid-cols-1 w-full space-y-6">
                <x-table-card
                    title="Recent Orders"
                    :headers="$productsTableHeaders"  
                    :data="$productsTableData"        
                />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <x-line-graph-card
            title="Raw Coffee Defect Count Trends"
            line-chart-i-d="lineChart"
            :chart-data="$lineChartData"
            :chart-categories="$lineChartCategories"
            />

            <x-recent-reports-card
                title="Recent Reports"
                :reports="$recentReports"
                class="min-h-[200px]" 
            />

           
        </div>



        
    </div>
</div>