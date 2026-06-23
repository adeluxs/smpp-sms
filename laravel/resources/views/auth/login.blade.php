@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-4">Login</h2>
    
    <form method="POST" action="/login">
        @csrf
        <div class="mb-4">
            <label>Email</label>
            <input type="email" name="email" class="border p-2 w-full" required>
        </div>
        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="border p-2 w-full" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 w-full">Login</button>
    </form>
</div>
@endsection