<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KingsChatController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\UsersController;
use App\Mail\BroadcastMail;
use App\Models\EmailDispatchRecipient;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Messaging Routes
    Route::prefix('messaging')->group(function () {
        Route::get('/kingschat', [KingsChatController::class, 'kingschat'])->name('messaging.kingschat');
        Route::get('/email', [EmailController::class, 'index'])->name('messaging.email');
        Route::get('/email/{dispatch}', [EmailController::class, 'show'])->name('messaging.email.show');
        Route::get('/users', [UsersController::class, 'users'])->name('messaging.users');
        Route::get('/users/search', [UsersController::class, 'search'])->name('messaging.users.search');

        
        // API Routes for sending messages
        Route::post('/kingschat/broadcast', [KingsChatController::class, 'sendKingschat'])->name('messaging.kingschat.send');
        Route::post('/email/broadcast', [EmailController::class, 'store'])->name('messaging.email.send');
        Route::post('/kingschat/credentials', [KingsChatController::class, 'updateCredentials'])->name('messaging.kingschat.credentials');

        Route::post('/messaging/email/test', [EmailController::class, 'test'])
            ->name('email.test');
    });
});

Route::get('/test', function () {
    $user = UserList::find(1);
    
    return new BroadcastMail(
        messageContent: [
            'title' => "Alexander",
            'subject' => 'Test Subject',
            'message' => '
            Hello {{full_name}}

            1 DAY TO GO📢📢
                NO ONE FORGOTTEN LIVE!!
                - COMPLETING THE FULL PREACHING OF THE GOSPEL IN THE PRISONS

                🗓️ TUESDAY, MARCH, 11TH
                🕙 10AM GMT+1

                REGISTRATION LINK: https://www.kingsforms.online/prison-ministry-conference

                PARTICIPATION LINK-
                www.nooneforgotten.org',
            'name' => 'Test Name',
            'user' => $user
        ],
        bannerImage: 'https://cdn1.kingschat.online/uploads/media/5d08b3bd2475aa0001121c37/SGZhd3A2N3dYNURkMlNiUnEvdkQrZz09/1000186526.jpg'
    );
});

Route::get('/test2', function () {
    $record = EmailDispatchRecipient::create([
        'dispatch_id' => 1,
        'email' => 'test@test.com',
        'status' => 'pending',
        'unsubscribe_token' => "rueuruyyhuee",
    ]);

    return $record;
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
