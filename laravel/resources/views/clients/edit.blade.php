@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Edit SMPP Client</h2>

    <form method="POST" action="/admin/clients/{{ $client->id }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label>System ID (read-only)</label>
            <input type="text" value="{{ $client->system_id }}" class="border p-2 w-full bg-gray-100" readonly>
        </div>
        <div class="mb-4">
            <label>Sender ID</label>
            <input type="text" name="sender_id" value="{{ $client->sender_id }}" class="border p-2 w-full" maxlength="11">
        </div>
        <div class="mb-4">
            <label>Throughput Limit</label>
            <input type="number" name="throughput_limit" value="{{ $client->throughput_limit }}" class="border p-2 w-full">
        </div>
        <div class="mb-4">
            <label>Status</label>
            <select name="status" class="border p-2 w-full">
                <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="disabled" {{ $client->status === 'disabled' ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Update</button>
    </form>
</div>
@endsection