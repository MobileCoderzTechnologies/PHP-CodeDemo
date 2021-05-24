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
     * create customer
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
}