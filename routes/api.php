<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/send-otp', 'LoginController@sendVerificationCode');
Route::post('/login-with-otp', 'LoginController@verifyOTP');
Route::post('/sign-in', 'LoginController@signIn');
Route::post('/add-account-details', 'LoginController@addAccount')->middleware('authenticateUser');
Route::post('/logout', 'LoginController@logout')->middleware('authenticateUser');
Route::post('/locate-me', 'ProfileController@locateMe')->middleware('authenticateUser');
Route::post('/change-password', 'ProfileController@changePassword')->middleware('authenticateUser');
Route::post('/update-profile', 'ProfileController@updateProfile')->middleware('authenticateUser');

