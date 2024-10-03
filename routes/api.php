<?php

use App\Http\Controllers\ContactPersonProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\FamilyController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'index']);




Route::get('/users', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'show']);
// Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);


Route::get('/professions', [UserController::class, 'getProfessions']);


Route::get('/qualifications', [UserController::class, 'getQualifications']);


Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working ',
    ], 200);
});


Route::post('/forgot-password', [ForgotPasswordController::class, 'generateOtp']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'generateOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);




Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('/login', [LoginController::class, 'login']);





Route::get('countries', [LocationController::class, 'getCountries']); // Get all countries
Route::get('states/{country_id}', [LocationController::class, 'getStates']); // Get states by country ID
Route::get('cities/{state_id}', [LocationController::class, 'getCities']); // Get cities by state ID

Route::get('locations', [LocationController::class, 'getLocations']); // Get all countries with states and cities


Route::get('/user/{userId}/locations', [LocationController::class, 'getUserLocations']);

Route::post('/user/{userId}/locations', [LocationController::class, 'addUserLocation']);

Route::get('/my-profile',[LocationController::class, 'index'])->name('my-profile');
Route::get('/users/{id}/contact-persons', [UserController::class, 'getContactPersons']);

// Route::get('/users/contact', [ContactPersonProfileController ::class, 'getContactPersons']);

// Route::get('/customer/{id}', [ContactPersonProfileController ::class, 'getContactPersons']);



use App\Http\Controllers\ProfileController;

Route::get('/new_profile/{id}', [ProfileController::class, 'getProfile']);



Route::get('/marital-status-list', [ProfileController::class, 'getUsersByMaritalStatus']);


Route::get('count_qualification', [UserController::class, 'getUserCount']);

Route::get('/count_location', [UserController::class, 'getUserCountByLocation']);

Route::get('/count_age_group', [UserController::class, 'getUserCountByAgeGroup']);

Route::get('/search-users', [UserController::class, 'searchUsers']);


// routes/api.php
// $router->get('/users/new-registrations', 'UserController@getNewRegistrations');




// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/profiles', [ProfileController::class, 'getProfiles']);
// });
