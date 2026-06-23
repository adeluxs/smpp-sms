<?php

namespace App\Http\Controllers\Admin;

use App\Models\SmppClient;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = SmppClient::with('tenant')->paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'system_id' => 'required|unique:smpp_clients',
            'password' => 'required|min:8',
            'sender_id' => 'nullable|string|max:11',
            'throughput_limit' => 'required|integer|min:1',
        ]);

        $client = SmppClient::create([
            ...$validated,
            'password_hash' => hash('sha256', $validated['password']),
        ]);

        return redirect()->route('admin.clients.index');
    }

    public function edit(SmppClient $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, SmppClient $client)
    {
        $validated = $request->validate([
            'sender_id' => 'nullable|string|max:11',
            'throughput_limit' => 'required|integer|min:1',
            'status' => 'required|in:active,suspended,disabled',
        ]);

        $client->update($validated);
        return redirect()->route('admin.clients.index');
    }
}