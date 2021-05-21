<?php

namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Exception;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;

/*
|=================================================================
| @Class        :   LoginService
| @Description  :   This class is reponsible for all authentication related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   21-April-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class LoginService 
{

    /**
   * send verification code
   * @param $request
   * @return $response
   */

  public function sendVerificationCode($request){

    $users = user::where('phone', $request->phone)->where('username', '!=', null)->get();

    if(count($users)>4){
        return response(["status"=>false, 'message'=>"Alredy five account have been registerd with this mobile number"], 404);                        
    }

    $user = user::where('username', null)->first();

    if(!$user){
        $user = new User();
    }

    $user->phone = $request->phone;
    $user->otp = "0000";
    $user->save();
    // code to send esms will come here.
    return response(["status"=>true, "message"=>"An otp has been sent successfully to the given mobile number"]);
  }

  /**
   * verify account
   * @param $request
   * @return $response
   */

  public function verifyOTP($request){

    $user = User::where('phone', $request->phone)->where('username', null)->first();

    if(!$user){
        return response(["status"=>false, 'message'=>"Invalid mobile number"], 404);                        
    }

    if($request->otp == $user->otp){
        $user->phone_verified_at = Carbon::now();
        $user->otp=null;
        $user->save();

        $res_user = new \StdClass();
        $res_user->id = $user->id;
        $res_user->phone = $user->phone;

        $expireDate=Carbon::now()->addDays(30)->timestamp;
        $res_user->exp = $expireDate;  

        $jwt = JWT::encode($res_user, "jwtToken");        
        $res_user->jwtToken = $jwt;  

        return response(['status'=>true, 'user'=> $res_user]);

        //return response(["status"=>true, "message"=>"Account Verified successfully"]);
    }

    else{
        return response(["status"=>false, "message"=>"Invalid otp"], 422);
    }

  }

  /**
   * add account
   * @param $request
   * @return $response
   */

  public function addAccount($request){
    $user = $request->user;
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->username = $request->username;
    $user->gender = $request->gender;
    $user->job = $request->job;
    $user->dob = $request->dob;
    $user->about_yourself = $request->about_yourself;
    $user->lat = $request->lat;
    $user->long = $request->long;
    $user->device_type = $request->device_type;
    $user->device_token = $request->device_token;
    $user->password = bcrypt($request->password);      

    if($request->profile_pic){
    $user->profile_pic = $this->saveFile($request->file('profile_pic'));
    }

    $user->save();
    $user->profile_pic = asset('storage/images/'.$user->profile_pic);
    return $user;
  }

  /******************************End**********************************************/

  /************************************To save any file*******************************/
    public function saveFile($file){
        $ext = $file->guessExtension();
        $file_name = 'image-'.uniqid()."-"."{$ext}";
        $file_url = "storage/images/";
        $file->move($file_url, $file_name);
        return $file_name;
    }
  
  /**************************************End******************************************/
}
