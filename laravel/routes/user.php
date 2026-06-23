use App\Http\Controllers\User\DashboardController;

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/messages', function () {
        return view('user.messages');
    })->name('user.messages');
    
    Route::get('/wallets', function () {
        return view('user.wallet');
    })->name('user.wallet');
});

Route::get('/smpp-credentials', function () {
    $client = auth()->user()->tenant->smppClients()->first();
    return view('user.smpp-credentials', compact('client'));
})->middleware(['auth'])->name('smpp.credentials');