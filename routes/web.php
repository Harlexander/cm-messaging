<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KingsChatController;
use App\Http\Controllers\MessagingController;
use App\Models\KcHandle;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Messaging Routes
    Route::prefix('messaging')->group(function () {
        Route::get('/kingschat', [KingsChatController::class, 'kingschat'])->name('messaging.kingschat');
        Route::get('/email', [MessagingController::class, 'email'])->name('messaging.email');
        Route::get('/users', [MessagingController::class, 'users'])->name('messaging.users');

        
        // API Routes for sending messages
        Route::post('/kingschat/broadcast', [KingsChatController::class, 'sendKingschat'])->name('messaging.kingschat.send');
        Route::post('/email/broadcast', [MessagingController::class, 'sendEmail'])->name('messaging.email.send');
        Route::post('/kingschat/credentials', [KingsChatController::class, 'updateCredentials'])->name('messaging.kingschat.credentials');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
