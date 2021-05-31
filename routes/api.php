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

/**************************General APIs for all users**********************************************/
Route::post('/send-otp', 'LoginController@sendVerificationCode');
Route::post('/login-with-otp', 'LoginController@verifyOTP');
Route::post('/sign-in', 'LoginController@signIn');
Route::post('/forget-password', 'LoginController@forgetPassword');
Route::post('/verify-forget-password-otp', 'LoginController@verifyForgetOTP');
Route::post('/reset-password', 'LoginController@resetPassword')->middleware('authenticateUser');
Route::post('/logout', 'LoginController@logout')->middleware('authenticateUser');
Route::post('/change-password', 'ProfileController@changePassword')->middleware('authenticateUser');
/*******************************************End******************************************************/

/***************************************Personal Account API*****************************************/
Route::post('/add-account-details', 'LoginController@addAccount')->middleware('authenticateUser');
Route::post('/update-profile', 'ProfileController@updateProfile')->middleware('authenticateUser');
Route::post('/locate-me', 'ProfileController@locateMe')->middleware('authenticateUser');
/*******************************************End******************************************************/


/***************************************Business Account API*******************************************************/
Route::post('/complete-business-profile', 'LoginController@completeBusinessProfile')->middleware('authenticateUser');
Route::post('/update-business-profile', 'ProfileController@updateBusinessProfile')->middleware('authenticateUser');
Route::get('/get-business-addesses', 'ProfileController@getBusinessAddresses')->middleware('authenticateUser');
Route::post('/add-business-address', 'ProfileController@addBusinessAddress')->middleware('authenticateUser');
Route::put('/update-business-address', 'ProfileController@updateBusinessAddress')->middleware('authenticateUser');
Route::delete('/delete-business-address', 'ProfileController@deleteBusinessAddress')->middleware('authenticateUser');
Route::get('/get-business-types', 'ProfileController@getBusinessTypes');
/*******************************************End*********************************************************************/




