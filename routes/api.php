<?php

use Elbytes\NovaTwoFactor\Http\Controller\TwoFactorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lifeonscreen\Google2fa\Google2FAAuthenticator;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/register', [TwoFactorController::class,'registerUser']);

Route::match(['get','post'],'/recover', [TwoFactorController::class,'recover'])->name('nova-two-factor.recover');
Route::get('/resend-code', [TwoFactorController::class,'resendEmail'])->name('nova-two-factor.resend-code');

Route::get('/status', [TwoFactorController::class,'getStatus']);

Route::post('/confirm', [TwoFactorController::class,'verifyOtp']);

Route::post('/toggle', [TwoFactorController::class,'toggle2Fa']);

Route::post('/reset-2fa', [TwoFactorController::class,'reset2Fa']);

Route::post('/send-otp-email', [TwoFactorController::class,'sendOtpEmail']);

Route::post('/authenticate', [TwoFactorController::class,'authenticate'])->name('nova-two-factor.auth');
