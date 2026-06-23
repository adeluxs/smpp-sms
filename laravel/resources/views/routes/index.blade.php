@extends('layouts.app')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Routing Rules</h2>
    
    <a href="/admin/routes/create" class="bg-blue-600 text-white px-4 py-2 mb-4 inline-block">Add Route</a>
    
    <table class="w-full border">
        <thead class="bg-gray-50">
            <tr>
                <th class="border p-2">Name</th>
                <th class="border p-2">Type</th>
                <th class="border p-2">Priority</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routes as $route)
            <tr>
                <td class="border p-2">{{ $route->name }}</td>
                <td class="border p-2">{{ $route->type }}</td>
                <td class="border p-2">{{ $route->priority }}</td>
                <td class="border p-2">{{ $route->enabled ? 'Enabled' : 'Disabled' }}</td>
                <td class="border p-2">
                    <a href="/admin/routes/{{ $route->id }}/edit" class="text-blue-600">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection