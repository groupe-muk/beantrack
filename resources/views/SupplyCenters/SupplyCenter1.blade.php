@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">{{ $SupplyCenter->name }}</h1>
    @if(isset($managerName))
        <p class="mb-2"><span class="font-semibold">Manager:</span> {{ $managerName }}</p>
    @endif
    <p class="mb-6"><span class="font-semibold">Number of Workers:</span> {{ $SupplyCenter->workers->count() }}</p>

    
    <form action="{{ route('workers.store') }}" method="POST" class="mb-6 flex gap-2">
        @csrf
        <input type="text" name="name" placeholder="Worker Name" required class="border p-2 rounded">
        <input type="hidden" name="warehouse_id" value="{{ $SupplyCenter->id }}">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Add Worker</button>
    </form>

    
    <h2 class="font-semibold mb-2">Transfer Worker</h2>
    <form action="{{ route('workers.transfer', 0) }}" method="POST" class="mb-6 flex gap-2" id="transferForm">
        @csrf
        <select name="worker_id" id="worker_id" required class="border p-2 rounded">
            <option value="">Select Worker</option>
            @foreach($SupplyCenter->workers as $worker)
                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
            @endforeach
        </select>
        <select name="warehouse_id" required class="border p-2 rounded">
            <option value="">To Warehouse</option>
            @foreach($allSupplyCenters as $SC)
                @if($SC->id !== $SupplyCenter->id)
                    <option value="{{ $SC->id }}">{{ $SC->name }}</option>
                @endif
            @endforeach
        </select>
        <button class="bg-yellow-600 text-white px-4 py-2 rounded">Transfer</button>
    </form>

  
    <h2 class="font-semibold mb-2">Workers</h2>
    <table class="min-w-full bg-white border mb-6">
        <thead>
            <tr>
                <th class="border px-4 py-2">Name</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($SupplyCenter->workers as $worker)
                <tr>
                    <td class="border px-4 py-2">{{ $worker->name }}</td>
                    <td class="border px-4 py-2">
                        <form action="{{ route('workers.destroy', $worker) }}" method="POST" onsubmit="return confirm('Delete this worker?');">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.getElementById('worker_id').addEventListener('change', function() {
        let form = document.getElementById('transferForm');
        let workerId = this.value || 0;
        form.action = "{{ url('workers') }}/" + workerId + "/transfer";
    });
</script>
@endsection