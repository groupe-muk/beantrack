<div class="bg-light-background min-h-screen dark:bg-dark-background">
    <p class="text-soft-brown pb-5">Manage your coffee bean production and delivery</p>
    <div class="w-full flex flex-col space-y-8">

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <x-stats-card
                    title="Active Orders"
                    value="{{ $supplierStats['activeOrders']['value'] }}"
                    iconClass="fa-cube"
                    changeText="{{ $supplierStats['activeOrders']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $supplierStats['activeOrders']['change']['direction'] }}"
                    changeType="{{ $supplierStats['activeOrders']['change']['type'] }}"
                />
                <x-stats-card
                    title="Total Inventory"
                    value="{{ $supplierStats['totalInventory']['value'] }}"
                    iconClass="fa-weight-hanging"
                    unit="kg"
                    changeText="{{ $supplierStats['totalInventory']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $supplierStats['totalInventory']['change']['direction'] }}"
                    changeType="{{ $supplierStats['totalInventory']['change']['type'] }}"
                />
                <x-stats-card
                    title="Pending Deliveries"
                    value="{{ $supplierStats['pendingDeliveries']['value'] }}"
                    iconClass="fa-calendar-alt"
                    unit="shipments"
                    changeText="{{ $supplierStats['pendingDeliveries']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $supplierStats['pendingDeliveries']['change']['direction'] }}"
                    changeType="{{ $supplierStats['pendingDeliveries']['change']['type'] }}"
                />
                <x-stats-card
                    title="Performance Score"
                    value="{{ $supplierStats['qualityScore']['value'] }}"
                    iconClass="fa-star"
                    changeText="{{ $supplierStats['qualityScore']['change']['percentage'] }}% from last period"
                    changeIconClass="fa-arrow-{{ $supplierStats['qualityScore']['change']['direction'] }}"
                    changeType="{{ $supplierStats['qualityScore']['change']['type'] }}"
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

            <x-progress-card
            title="Inventory Status"
            :items="$inventoryItems"
            />

            </div>
        </div>

        <div class="w-full space-y-6">

                <x-table-card
                    title="Recent Orders"
                    :headers="$productsTableHeaders"  
                    :data="$productsTableData"        
                />
        </div>

    </div>    

</div>    