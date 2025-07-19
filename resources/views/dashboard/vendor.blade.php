<div class="bg-light-background min-h-screen dark:bg-dark-background">
    <p class="text-soft-brown pb-5">Browse and purchase coffee from the factory</p>
    <div class="w-full flex flex-col space-y-8">

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <x-stats-card
                    title="Active Orders"
                    value="{{ $vendorStats['activeOrders']['value'] }}"
                    iconClass="fa-cube"
                    changeText="{{ $vendorStats['activeOrders']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $vendorStats['activeOrders']['change']['direction'] }}"
                    changeType="{{ $vendorStats['activeOrders']['change']['type'] }}"
                />
                <x-stats-card
                    title="Inventory"
                    value="{{ $vendorStats['totalInventory']['value'] }}"
                    iconClass="fa-weight-hanging"
                    unit="kgs"
                    changeText="{{ $vendorStats['totalInventory']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $vendorStats['totalInventory']['change']['direction'] }}"
                    changeType="{{ $vendorStats['totalInventory']['change']['type'] }}"
                />
                <x-stats-card
                    title="Orders in Transit"
                    value="{{ $vendorStats['ordersInTransit']['value'] }}"
                    iconClass="fa-truck"
                    unit="shipments"
                    changeText="{{ $vendorStats['ordersInTransit']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $vendorStats['ordersInTransit']['change']['direction'] }}"
                    changeType="{{ $vendorStats['ordersInTransit']['change']['type'] }}"
                />
                <x-stats-card
                    title="Number of Warehouses"
                    value="{{ $vendorStats['warehouseCount']['value'] }}"
                    iconClass="fa-warehouse"
                    unit=""
                    changeText="{{ $vendorStats['warehouseCount']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $vendorStats['warehouseCount']['change']['direction'] }}"
                    changeType="{{ $vendorStats['warehouseCount']['change']['type'] }}"
                />
            </div>
        </div>

        <div class="space-y-6">

         <x-orders-card
            title="Pending Orders"
            :orders="$pendingOrders"
            class="min-h-[300px]"
            :fullWidth="true"
        />

        </div> 
        
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
            
            <x-progress-card
            title="Inventory Status"
            :items="$inventoryItems"
            />

            <x-recent-reports-card
                title="Recent Reports"
                :reports="$recentReports"
                class="min-h-[200px]" 
            />

            </div>
        </div>

    </div>    

</div>    