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
Route::get('/get-businesses-near-me', 'ProfileController@businessesNearMe')->middleware('authenticateUser');
Route::get('/get-businesses-near-me', 'ProfileController@businessesNearMe')->middleware('authenticateUser');
Route::post('/follow-unfollow-business', 'ProfileController@followUnfollowBusiness')->middleware('authenticateUser');
Route::post('/follow-unfollow-user', 'ProfileController@followUnfollowUser')->middleware('authenticateUser');
Route::post('/sync-contacts', 'ProfileController@syncContacts')->middleware('authenticateUser');
Route::post('/add-friends', 'ProfileController@addFriends')->middleware('authenticateUser');
Route::get('/get-friends', 'ProfileController@getFriends')->middleware('authenticateUser');
Route::post('/invite-friends', 'ProfileController@inviteFriends')->middleware('authenticateUser');
Route::get('/get-plinkd-locations', 'ProfileController@getPlinkdLocations')->middleware('authenticateUser');
Route::get('/get-all-followers', 'ProfileController@getallFollowers')->middleware('authenticateUser');
Route::get('/get-followed-businesses', 'ProfileController@getFollowedBusinesses')->middleware('authenticateUser');
Route::get('/get-follwer-requests', 'ProfileController@getFollowerRequests')->middleware('authenticateUser');
Route::post('/add-story', 'StoryController@addStory')->middleware('authenticateUser');
Route::get('/my-stories', 'StoryController@myStories')->middleware('authenticateUser');
Route::get('/story-details', 'StoryController@storyDetails')->middleware('authenticateUser');
Route::delete('/delete-story', 'StoryController@deleteStory')->middleware('authenticateUser');
Route::get('/get-recent-stories', 'StoryController@recentStories')->middleware('authenticateUser');
Route::post('/view-story', 'StoryController@viewStory')->middleware('authenticateUser');
Route::post('/like-unlike-story', 'StoryController@likeStory')->middleware('authenticateUser');
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




