@extends('layouts.app')

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-sm text-gray-500">Current Balance</h3>
        <p class="text-2xl font-bold">${{ number_format($wallet->balance ?? 0, 2) }}</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-sm text-gray-500">Messages Sent Today</h3>
        <p class="text-2xl font-bold">{{ $stats['sent'] ?? 0 }}</p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-sm text-gray-500">Delivery Rate</h3>
        <p class="text-2xl font-bold">{{ $stats['delivery_rate'] ?? 0 }}%</p>
    </div>
</div>

<div class="bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4">Quick Send</h2>
    
    <form id="send-form" class="space-y-4">
        <div>
            <label>To (E.164 format)</label>
            <input type="tel" name="to" class="border p-2 w-full" placeholder="+15551234567" required>
        </div>
        <div>
            <label>Message</label>
            <textarea name="message" class="border p-2 w-full" rows="3" required></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Send SMS</button>
    </form>
    
    <div id="result" class="mt-4 hidden">
        <p class="text-green-600">Message sent! ID: <span id="msg-id"></span></p>
    </div>
</div>

<script>
document.getElementById('send-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form));
    
    const resp = await fetch('/api/v1/send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify(data)
    });
    
    const result = await resp.json();
    if (resp.ok) {
        document.getElementById('msg-id').textContent = result.message_id;
        document.getElementById('result').classList.remove('hidden');
    }
});
</script>
@endsection