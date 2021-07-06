<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\ProfileService;
use Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;

/*
|=================================================================
| @Class        :   ProfileController
| @Description  :   This class is reponsible for all profile related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   24-May-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class ProfileController extends Controller
{
    protected $profileService;
	public function __construct(ProfileService $profileService) {
		$this->profileService = $profileService;
    } 


    /**
     * locate me
     * @param Request $request
     * @return $response
     */
    
    public function locateMe(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',  
            'long' => 'required'
            ]);
            
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator);
            }
            else{
                try{
                    $response = $this->profileService->locateMe($request);
                    return $this->respondWithSuccess($response);
                }
                catch(Exception $e){
                    return $this->respondWithInternalServerError($e->getMessage());
                }
            }
    }

    /**
     * change password
     * @param Request $request
     * @return $response
     */
    
    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required',
            'confirm_new_password'=>'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        else{
            try{
                $response = $this->profileService->changePassword($request);
                return $response;
            }
            catch(Exception $e){
                return $this->respondWithInternalServerError($e->getMessage());
            }
        }
    }

    /**
     * update profile
     * @param Request $request
     * @return $response
     */

    public function updateProfile(Request $request){
        try{
            $user = $this->profileService->updateProfile($request);
            return $this->respondWithSuccess($user);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

     /**
     * update business profile
     * @param Request $request
     * @return $response
     */

    public function updateBusinessProfile(Request $request){
        try{
            $user = $this->profileService->updateBusinessProfile($request);
            return $this->respondWithSuccess($user);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

    /**
     * get business addresses
     * @param Request $request
     * @return $response
    */

    public function getBusinessAddresses(Request $request){
        try{
            return $this->respondWithSuccess($request->user->addresses);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

     /**
     * add business address
     * @param Request $request
     * @return $response
    */

    public function addBusinessAddress(Request $request){

        $validator = Validator::make($request->all(), [
            'address_name' => 'required',
            'city'  => 'required',
            'postal_code' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $address = $this->profileService->addBusinessAddress($request);
            return $this->respondWithSuccessMessage("Address added successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

     /**
     * update business address
     * @param Request $request
     * @return $response
    */

    public function updateBusinessAddress(Request $request){
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $address = $this->profileService->updateBusinessAddress($request);

            if($address){
                return $this->respondWithSuccessMessage("Address updated successfully");
            }

            else{
                return response()->json(['status' => false, 'message' => 'Invalid address id.'], 401);            
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

     /**
     * delete business address
     * @param Request $request
     * @return $response
    */

    public function deleteBusinessAddress(Request $request){
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $address = $this->profileService->deleteBusinessAddress($request);

            if($address){
                return $this->respondWithSuccessMessage("Address deleted successfully");
            }

            else{
                return response()->json(['status' => false, 'message' => 'Invalid address id.'], 401);            
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

    /**
     * get business types
     * @param Request $request
     * @return $response
    */
    public function getBusinessTypes(){
        try{
            $businessTypes = $this->profileService->getBusinessTypes();
            return $this->respondWithSuccess($businessTypes);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

    /**
     * get businesses near by me
     * @param Request $request
     * @return $response
    */
    public function businessesNearMe(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $businesses = $this->profileService->businessesNearMe($request);

            return $this->respondWithSuccess($businesses);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }


     /**
     * follow unfollow business
     * @param Request $request
     * @return $response
    */

    public function followUnfollowBusiness(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->profileService->followUnfollow($request, $request->business_id);

            if($response === "followed"){
                return $this->respondWithSuccessMessage("Follower request sent successfully");
            }

            else{
                return $this->respondWithSuccessMessage("Removed from your follower list");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

    /**
     * follow unfollow user
     * @param Request $request
     * @return $response
    */

    public function followUnfollowUser(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->profileService->followUnfollow($request, $request->user_id);

            if($response === "followed"){
                return $this->respondWithSuccessMessage("Follower request sent successfully");
            }

            else{
                return $this->respondWithSuccessMessage("Removed from your follower list");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }


      /**
     * sync user contacts
     * @param Request $request
     * @return $response
    */
    public function syncContacts(Request $request){
        $validator = Validator::make($request->all(), [
            'contacts' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $contacts = $this->profileService->syncContacts($request);

            return $this->respondWithSuccess($contacts);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * add friends
     * @param Request $request
     * @return $response
    */
    public function addFriends(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $contacts = $this->profileService->addFriends($request);
            if($contacts){
                return $this->respondWithSuccessMessage("Friends added successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid users provided");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get friends
     * @param Request $request
     * @return $response
    */
    public function getFriends(Request $request){
        try{
            $friends = $this->profileService->getFriends($request);
            if($friends){
                return $this->respondWithSuccess($friends);
            }
            else{
                return $this->respondWithSuccessMessage("Invalid users provided");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * invite friends
     * @param Request $request
     * @return $response
    */

    public function inviteFriends(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required|array',
            'location_type' => 'required',
            'address_name' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->profileService->inviteFriends($request);
            if($response){
                return $this->respondWithSuccessMessage("Invitations sent successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid users provided");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get plinkd locations
     * @param Request $request
     * @return $response
    */

    public function getPlinkdLocations(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $locations = $this->profileService->getPlinkdLocations($request);
            return $this->respondWithSuccess($locations);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get all followers
     * @param Request $request
     * @return $response
    */
    public function getallFollowers(Request $request){
        try{
            $response = $this->profileService->getallFollowers($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get followed businesses
     * @param Request $request
     * @return $response
    */
    public function getFollowedBusinesses(Request $request){
        try{
            $response = $this->profileService->getFollowedBusinesses($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get follower requests
     * @param Request $request
     * @return $response
    */
    public function getFollowerRequests(Request $request){
        try{
            $response = $this->profileService->getFollowerRequests($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * accept and reject the request
     * @param Request $request
     * @return $response
    */
    public function acceptRejectRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'action'=> 'required|in:accept,reject|string|',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->acceptRejectRequest($request);
            if($response === "accepted"){
                return $this->respondWithSuccessMessage("Accepted successfuly");
            }

            else{
                return $this->respondWithSuccessMessage("Rejected successfuly");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * change story privacy
     * @param Request $request
     * @return $response
    */
    public function changeStoryPrivacy(Request $request){
        $validator = Validator::make($request->all(), [
            'status'=> 'required|in:public,friends,custom|string|',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->changeStoryPrivacy($request);
            return $this->respondWithSuccessMessage("Story privacy updated successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

      /**
     * change location privacy
     * @param Request $request
     * @return $response
    */
    public function changeLocationPrivacy(Request $request){
        $validator = Validator::make($request->all(), [
            'status'=> 'required|in:public,private|string|',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->changeLocationPrivacy($request);
            return $this->respondWithSuccessMessage("Location privacy updated successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * change profile privacy
     * @param Request $request
     * @return $response
    */
    public function changeProfilePrivacy(Request $request){
        $validator = Validator::make($request->all(), [
            'status'=> 'required|in:public,private|string|',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->changeProfilePrivacy($request);
            return $this->respondWithSuccessMessage("Profile privacy updated successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * on off locationService
     * @param Request $request
     * @return $response
    */
    public function onOffLocationService(Request $request){
        try{
            $response = $this->profileService->onOffLocationService($request);
            return $this->respondWithSuccessMessage("Location Service setting updated successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * on off locationService
     * @param Request $request
     * @return $response
    */
    public function onOffNotifications(Request $request){
        try{
            $response = $this->profileService->onOffNotifications($request);
            return $this->respondWithSuccessMessage("Notifications setting updated successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }


    /**
     * get setting details
     * @param Request $request
     * @return $response
    */
    public function getSettingDetails(Request $request){
        return $this->respondWithSuccess($request->user->setting);
    }

    /**
     * get profile info
     * @param Request $request
     * @return $response
    */
    public function getProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->getProfile($request);

            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid user id"], 401);                        
            }

            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get top places
     * @param Request $request
     * @return $response
    */
    public function topPlaces(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->topPlaces($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid user id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get stories
     * @param Request $request
     * @return $response
    */
    public function plinkds(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->plinkds($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid user id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get recent places
     * @param Request $request
     * @return $response
    */
    public function recentPlaces(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->recentPlaces($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid user id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * provide review
     * @param Request $request
     * @return $response
    */
    public function provideReview(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id'=> 'required',
            'video' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->provideReview($request);
            return $this->respondWithSuccessMessage("Review provided Successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * recently added friends
     * @param Request $request
     * @return $response
    */
    public function recentlyAddedFriends(Request $request){
        try{
            $friends = $this->profileService->recentlyAddedFriends($request);
            return $this->respondWithSuccess($friends);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * get business profile info
     * @param Request $request
     * @return $response
    */
    public function getBusinessProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->getBusinessProfile($request);

            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid business id"], 401);                        
            }

            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get plinkds by business
     * @param Request $request
     * @return $response
    */
    public function plinkdsByBusiness(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->plinkdsByBusiness($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid business id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * get plinkds on business
     * @param Request $request
     * @return $response
    */
    public function plinkdsOnBusiness(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->plinkdsOnBusiness($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid business id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

      /**
     * get reviews
     * @param Request $request
     * @return $response
    */
    public function reviews(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->profileService->reviews($request);
            if(!$response){
                return response(["status"=>false, 'message'=>"Invalid business id"], 401);                        
            }
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }
}