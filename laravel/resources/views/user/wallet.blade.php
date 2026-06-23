@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">My Wallet</h2>
    
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <label class="text-gray-500">Current Balance</label>
            <p class="text-3xl font-bold">${{ number_format($wallet->balance ?? 0, 2) }}</p>
        </div>
        <div>
            <label class="text-gray-500">Wallet Type</label>
            <p class="text-xl">{{ ucfirst($wallet->type ?? 'Prepaid') }}</p>
        </div>
    </div>
    
    <table class="w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="border p-2">Date</th>
                <th class="border p-2">Type</th>
                <th class="border p-2">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border p-2">Sample transaction</td>
                <td class="border p-2">Credit</td>
                <td class="border p-2">$100.00</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection