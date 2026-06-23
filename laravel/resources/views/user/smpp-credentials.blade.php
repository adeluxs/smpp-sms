@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">My SMPP Credentials</h2>
    
    @if($client)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="font-bold">System ID</label>
            <code class="block bg-gray-100 p-2">{{ $client->system_id }}</code>
        </div>
        <div>
            <label class="font-bold">Password</label>
            <code class="block bg-gray-100 p-2">{{ $client->password ?? '******' }}</code>
            <small class="text-gray-500">Save this now - it won't be shown again</small>
        </div>
        <div>
            <label class="font-bold">Sender ID</label>
            <code class="block bg-gray-100 p-2">{{ $client->sender_id ?? 'Not set - use source in API' }}</code>
        </div>
        <div>
            <label class="font-bold">Bind Mode</label>
            <code class="block bg-gray-100 p-2">{{ ucfirst($client->bind_mode) }}</code>
        </div>
    </div>
    
    <div class="mt-6 p-4 bg-blue-50 rounded">
        <p class="font-bold mb-2">Connection Endpoint:</p>
        <div class="font-mono text-sm">
            <p>Host: {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'your-server.com' }}</p>
            <p>Port: 2775 (SMPP)</p>
            <p>TLS: Yes (required)</p>
            <p>Vhost: / (default)</p>
        </div>
    </div>
    
    <div class="mt-4 p-4 bg-yellow-50 rounded">
        <p class="font-bold">Supported Operations:</p>
        <ul class="list-disc list-inside text-sm">
            <li>bind_transceiver</li>
            <li>submit_sm</li>
            <li>enquire_link</li>
            <li>deliver_sm (for DLRs)</li>
        </ul>
    </div>
    
    <a href="/my/smpp-credentials/reset" class="inline-block mt-4 text-blue-600" 
       onclick="return confirm('Reset password? Old password will stop working.')">
        Reset Password
    </a>
    @else
    <p>No SMPP credentials configured. <a href="/contact" class="text-blue-600">Contact support</a></p>
    @endif
</div>
@endsection