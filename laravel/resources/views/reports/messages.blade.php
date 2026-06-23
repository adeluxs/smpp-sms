@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Message Reports</h2>
    
    <table class="w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="border p-2">Status</th>
                <th class="border p-2">Count</th>
                <th class="border p-2">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($messages as $msg)
            <tr>
                <td class="border p-2">{{ $msg->status }}</td>
                <td class="border p-2">{{ $msg->count }}</td>
                <td class="border p-2">${{ number_format($msg->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection