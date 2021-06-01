<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;

/*
|=================================================================
| @Class        :   LoginController
| @Description  :   This class is reponsible for all authentication related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   21-May-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class LoginController extends Controller
{
    protected $loginService;
	public function __construct(LoginService $loginService) {
		
		$this->loginService = $loginService;
    } 


    /**
     * send verification code
     * @param Request $request
     * @return $response
     */
    
    public function sendVerificationCode(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required',  
            'device_token' => 'required',
            'account_type'=> 'required|in:personal,business|string|',
            ]);
            
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator);
            }
            else{
                try{
                    $response = $this->loginService->sendVerificationCode($request);
                    return $response;
                }
                catch(Exception $e){
                    return $this->respondWithInternalServerError($e->getMessage());
                }
            }
    }

    /**
     * verify account
     * @param Request $request
     * @return $response
     */
    
    public function verifyOTP(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required',  
            'otp' => 'required'
            ]);
            
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator);
            }
            try{
                $response = $this->loginService->verifyOTP($request);
                return $response;
            }
            catch(Exception $e){
                return $this->respondWithInternalServerError($e->getMessage());
            }
    }

    /**
     * create customer
     * @param Request $request
     * @return $response
     */

    public function addAccount(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users',
            'username'  => 'required|unique:users',
            'password' => 'required|min:8',
            'confirm_password'=>'required|same:password',
            'gender' => 'required',
            'job' => 'required',
            'dob'=> 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        
        try{
            $user = $this->loginService->addAccount($request);
            return $this->respondWithSuccess($user);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }   
    }

    /**
     * complete business profile
     * @param Request $request
     * @return $response
    */

    public function completeBusinessProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'business_name' => 'required',
            'business_type' => 'required',
            'business_address' => 'required',
            'city'  => 'required',
            'postal_code' => 'required',
            'lat' => 'required',
            'long' => 'required',
            'brief_description' => 'required',
            'services' => 'required',
            'web_url' => 'required',
            'email' => 'required|unique:users',
            'username'  => 'required|unique:users',
            'password' => 'required|min:8',
            'confirm_password'=>'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $user = $this->loginService->completeBusinessProfile($request);
            return $this->respondWithSuccess($user);
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

    /**
     * create customer
     * @param Request $request
     * @return $response
     */

    public function signIn(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',  
            'password' => 'required',
            'device_token' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        else{
            try{
                $user = $this->loginService->signIn($request);
                return $user;
            }
            catch(Exception $e){
                return $this->respondWithInternalServerError($e->getMessage());
            }
        }
        
    }

    /**
     * log out
     * @param Request $request
     * @return $message
     */

    public function logout(Request $request){
        try{
            $user = $this->loginService->logout($request);
            return $this->respondWithSuccessMessage("Logged out successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
        
    }

     /**
     * forget password
     * @param Request $request
     * @return $response
     */
    
    public function forgetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',  
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        else{
            try{
                $response = $this->loginService->forgetPassword($request);
                return $response;
            }
            catch(Exception $e){
                return $this->respondWithInternalServerError($e->getMessage());
            }
        }
    }

      /**
     * forget password otp verification 
     * @param Request $request
     * @return $response
     */
    
    public function verifyForgetOTP(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'otp'  =>  'required'
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        else{
            try{
                $response = $this->loginService->verifyForgetOTP($request);
                return $response;
            }
            catch(Exception $e){
                return $this->respondWithInternalServerError($e->getMessage());
            }
        }
    }

    /**
     * reset password
     * @param Request $request
     * @return $response
     */
    
    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [ 
            'otp' => 'required',
            'new_password' => 'required',
            'confirm_new_password'=>'required|same:new_password',
        ]);
            
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->loginService->resetPassword($request);
            return $response;
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }
}