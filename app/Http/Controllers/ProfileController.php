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

    public function getBusinessTypes(){
        try{
            $businessTypes = $this->profileService->getBusinessTypes();
            return $this->respondWithSuccess($businessTypes);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }
}