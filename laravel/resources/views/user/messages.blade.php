@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">My Messages</h2>
    
    <table class="w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="border p-2">To</th>
                <th class="border p-2">Content</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Price</th>
                <th class="border p-2">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $msg)
            <tr>
                <td class="border p-2">{{ $msg->destination }}</td>
                <td class="border p-2">{{ Str::limit($msg->content, 50) }}</td>
                <td class="border p-2">{{ $msg->status }}</td>
                <td class="border p-2">${{ $msg->price }}</td>
                <td class="border p-2">{{ $msg->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-4 text-center">No messages found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection