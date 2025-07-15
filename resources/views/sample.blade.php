@extends('layouts.app')

@section('content')
<div class="bg-soft-gray min-h-screen p-8 dark:bg-dark-background">
    <div class="max-w-7xl mx-auto flex flex-col space-y-8">

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-stats-card
                    title="Active Orders"
                    value="5"
                    iconClass="fa-cube"
                    unit="kgs"
                    changeText="5.2% from last period"
                    changeIconClass="fa-arrow-up"
                />             
            </div>
        </div>

        <div class="space-y-6">
            <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white">Reusable Chart Cards Example</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-column-graph-card
                    title="Daily Sales Overview"
                    column-chart-i-d="dailySalesChart"
                    :chart-data="$salesData"
                    :chart-data2="$salesData2"
                    :chart-categories="$salesCategories"
                    seriesName="Revenue (UGX)"
                    seriesName2="Cost (UGX)"
                />
            </div>
        </div>    

        <div class="space-y-6">
             <h2 class="text-3xl font-bold text-center text-coffee-brown dark:text-gray-200">Table</h2>
            <div class="grid grid-cols-1 w-full">
                <x-table-card
                    title="Recent Products"
                    :headers="$productsTableHeaders"  {{-- Data passed as prop --}}
                    :data="$productsTableData"        {{-- Data passed as prop --}}
                />
            </div> 
        </div> 
        
        <div class="space-y-6">
             
        <x-progress-card
            title="Inventory Status"
            :items="$inventoryItems"
        />

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-orders-card
                title="Pending Orders"
                :orders="$pendingOrders"
                class="min-h-[300px]" 
            />
        </div>

        <x-ml-prediction-graph-card
            title="Demand Forecast Analytics"
            chart-title="Coffee Demand Predictions & Historical Trends"
            prediction-chart-i-d="mlPredictionsChart"
            :chart-data="$mlPredictionData"
            :chart-categories="$mlPredictionCategories"
            :description="$mlPredictionDescription"
            class="h-[600px]" {{-- Adjust height as needed for visual balance --}}
        />

        <x-line-graph-card
        
            title="Quality Score"
            line-chart-i-d="lineChart"
            :chart-data="$lineChartData"
            :chart-categories="$lineChartCategories"

        />

    </div>
</div>
@endsection