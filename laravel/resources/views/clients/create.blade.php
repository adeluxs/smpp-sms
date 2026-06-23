@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Create SMPP Client</h2>

    <form method="POST" action="/admin/clients">
        @csrf
        <div class="mb-4">
            <label>System ID</label>
            <input type="text" name="system_id" class="border p-2 w-full" required>
        </div>
        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="border p-2 w-full" required>
        </div>
        <div class="mb-4">
            <label>Sender ID</label>
            <input type="text" name="sender_id" class="border p-2 w-full" maxlength="11">
        </div>
        <div class="mb-4">
            <label>Throughput Limit (msgs/sec)</label>
            <input type="number" name="throughput_limit" class="border p-2 w-full" value="100">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Create</button>
    </form>
</div>
@endsection