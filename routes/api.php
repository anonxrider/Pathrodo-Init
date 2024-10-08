<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ModeController;
use App\Http\Controllers\VerificationController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
//     // User routes
// });

// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
//     // Admin routes
// });

//EMAIL ACTIVATE
Route::post('/email/verify-otp', [VerificationController::class, 'verifyOtp']);
Route::post('/email/resend-otp', [VerificationController::class, 'resendOtp']);

//FORGET PASSWORD EMAIL OTP SEND
Route::post('/forget-password', [AuthController::class, 'forgotPassword']);

// Route to reset the password using OTP
Route::post('reset-password', [AuthController::class, 'resetPassword']);

//REGISTER API
Route::post('/register', [AuthController::class, 'register']);

//REGISTER ADMIN API
Route::post('/pathrodo-admin', [AuthController::class, 'registerAdmin']);

//LOGIN API PUBLIC
Route::post('/login', [AuthController::class, 'login']);

//LOGIN ADMIN
Route::post('/pathrodo-login', [AuthController::class, 'loginAdmin']);

//GET ACTIVE BANNERS
Route::get('/active-home-banners', [BannerController::class, 'homeActiveBanners']);

//LIST ALL CATEGORIES
Route::get('/categories', [CategoryController::class, 'index']);

//LIST ALL Item Types
Route::get('/item-types', [ItemTypeController::class, 'index']);

//GET A CATEGORY BY CATEGORY ID
Route::get('/categories/{id}', [CategoryController::class, 'show']);

//GET A ITEM TYPE BY ITEM TYPE ID
Route::get('/item-type/{id}', [ItemTypeController::class, 'show']);

//LOGOUT
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route to deactivate the user account
Route::post('/deactivate-account', [AuthController::class, 'deactivateAccount'])->middleware('auth:sanctum');

// Route to delete the user account
Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');

//MY PROFILE
Route::post('/my-profile', [AuthController::class, 'getProfile'])->middleware('auth:sanctum');

//CHANGE PASSWORD
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

//EDIT OR UPDATE PROFILE
Route::post('/update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

//UPDATE OR CHANGE EMAIL
// Route::post('/update-email', [AuthController::class, 'updateEmail'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index']); // Get all user addresses
    Route::post('/addresses', [AddressController::class, 'store']); // Create an address
    Route::post('/addresses/{address}', [AddressController::class, 'update']); // Update an address
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']); // Delete an address


    Route::post('/categories', [CategoryController::class, 'store']); // Admin only
    Route::put('/categories/{id}', [CategoryController::class, 'update']); // Admin only
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']); // Admin only

    Route::post('/item-types', [ItemTypeController::class, 'store']); // Admin only

    Route::post('/banners', [BannerController::class, 'store']); // Admin only
    Route::get('/banners', [BannerController::class, 'index']); //Admin only

    //UNITS
    Route::get('/units', [UnitController::class, 'index']);
    Route::get('/units/{id}', [UnitController::class, 'show']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::put('/units/{id}', [UnitController::class, 'update']);
    Route::delete('/units/{id}', [UnitController::class, 'destroy']);

    //MODES
    Route::get('/modes', [ModeController::class, 'index']);
    Route::get('/modes/{id}', [ModeController::class, 'show']);
    Route::post('/modes', [ModeController::class, 'store']);
    Route::put('/modes/{id}', [ModeController::class, 'update']);
    Route::delete('/modes/{id}', [ModeController::class, 'destroy']);

    //CHECK USER
    Route::get('/check-user', [AuthController::class, 'getUser']);

});