@extends('layouts.app')

@section('content')
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-lg font-bold">Active Sessions</h3>
        <p class="text-3xl">Loading...</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-lg font-bold">Messages Today</h3>
        <p class="text-3xl">Loading...</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-lg font-bold">Queue Depth</h3>
        <p class="text-3xl">Loading...</p>
    </div>
</div>
@endsection