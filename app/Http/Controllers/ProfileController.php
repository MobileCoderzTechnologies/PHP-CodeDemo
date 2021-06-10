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

    public function followUnfollow(Request $request){
        $validator = Validator::make($request->all(), [
            'business_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->profileService->followUnfollow($request);

            if($response === "followed"){
                return $this->respondWithSuccessMessage("Added in you followers list");
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

    public function getPlinkdLocations(Request $request){
        try{
            $locations = $this->profileService->getPlinkdLocations($request);
            return $this->respondWithSuccess($locations);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }
}