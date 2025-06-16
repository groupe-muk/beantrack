@extends('layouts.app')

@section('content')

<div class="bg-soft-gray min-h-screen flex items-center justify-center">
    <x-stats-card
        title="Active Orders"
        value="5"
        iconClass="fa-cube" 
        unit="kgs"
        changeText="5.2% from last period"
        changeIconClass="fa-arrow-up"
    />
</div>

@endsection