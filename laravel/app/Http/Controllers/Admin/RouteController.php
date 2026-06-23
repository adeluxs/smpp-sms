<?php

namespace App\Http\Controllers\Admin;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with('provider')->paginate(20);
        return view('routes.index', compact('routes'));
    }
}