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
| @Created_at   :   21-April-2021
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
            'email' => 'required',
            'username'  => 'required|unique:users',
            'password' => 'required|min:8',
            'confirm_password'=>'required|same:password',
            'confirm_password' => 'required|min:8',
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
}