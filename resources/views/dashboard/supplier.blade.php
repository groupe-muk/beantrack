<div class="bg-light-background min-h-screen dark:bg-dark-background">
    <p class="text-soft-brown pb-5">Manage your coffee bean production and delivery</p>
    <div class="w-full flex flex-col space-y-8">

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <x-stats-card
                    title="Active Orders"
                    value="5"
                    iconClass="fa-cube"
                    unit="kgs"
                    changeText="5.2% from last period"
                    changeIconClass="fa-arrow-up"
                    changeType="positive"
                />
                <x-stats-card
                    title="Production Volume"
                    value="1,240"
                    iconClass="fa-weight-hanging"
                    unit="kg"
                    changeText="1.2% from last period"
                    changeIconClass="fa-arrow-down"
                    changeType="negative"
                />
                <x-stats-card
                    title="Delivery Schedule"
                    value="8"
                    iconClass="fa-calendar-alt"
                    unit="shipments"
                    changeText="2.1% from last period"
                    changeIconClass="fa-arrow-up"
                    changeType="positive"
                />
                <x-stats-card
                    title="Quality Score"
                    value="92/100"
                    iconClass="fa-star"
                    unit=""
                    changeText="0.5% from last period"
                    changeIconClass="fa-arrow-up"
                    changeType="positive"
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