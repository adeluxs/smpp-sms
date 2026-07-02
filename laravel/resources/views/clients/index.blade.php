@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">SMPP Clients</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">System ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Throughput</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Last Bind</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-sm">{{ $client->system_id }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($client->status === 'active') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($client->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $client->throughput_limit }}/sec</td>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $client->last_bind_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="px-4 py-2">
                        <a href="/admin/clients/{{ $client->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No clients found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($clients->hasPages())
    <div class="mt-4">{{ $clients->links() }}</div>
    @endif
</div>
@endsection