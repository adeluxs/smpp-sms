@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">SMPP Clients</h2>
    
    <table class="w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="border p-2">System ID</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Throughput</th>
                <th class="border p-2">Last Bind</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td class="border p-2">{{ $client->system_id }}</td>
                <td class="border p-2">{{ $client->status }}</td>
                <td class="border p-2">{{ $client->throughput_limit }}/s</td>
                <td class="border p-2">{{ $client->last_bind_at?->ago() ?? 'Never' }}</td>
                <td class="border p-2">
                    <a href="/admin/clients/{{ $client->id }}/edit" class="text-blue-600">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection