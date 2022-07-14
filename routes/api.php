<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\EmailVerificationController;
use App\Http\Controllers\api\FilesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [UserController::class, 'register_method']);
Route::post('/login', [UserController::class, 'login_method']);
Route::post('/forget-password', [UserController::class, 'forget_password_method'])->middleware('guest')->name('password.email');
Route::post('/reset-password', [UserController::class, 'reset_password_method'])->middleware('guest')->name('password.reset');
// reset password will be handled from web.php

Route::post('/email/verification-notification', [EmailVerificationController::class, 'send_email_to_verify_method'])->middleware(['auth:sanctum', 'throttle:6,1']);
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify_method']);
// verify password will be handled from web.php


Route::post('/file/upload', [FilesController::class, 'upload_method'])->middleware('auth:sanctum')->name('upload-files');
Route::post('/file/delete/{id}', [FilesController::class, 'delete_method'])->middleware('auth:sanctum')->name('upload-files');

// all private routes go here