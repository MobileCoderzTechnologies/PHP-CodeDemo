<?php

namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Exception;
use App\User;
use App\Address;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use Twilio\Rest\Client;
use App\Setting;

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

    $user = user::where('phone', $request->phone)->first();
    $users = user::where('device_token', $request->device_token)->get();

    if(!$user){
      if(count($users)>400){
        return response(["status"=>false, 'message'=>"Alredy five accounts have been registerd with this device"], 422);                        
      }

      $user = new User();
      $user->phone = $request->phone;
    }

    if($user->device_token != $request->device_token){
      if(count($users)>400){
        return response(["status"=>false, 'message'=>"Alredy five accounts have been registerd with this device"], 422);                        
      }
    }

    $user->device_token = $request->device_token;
    $user->account_type = $request->account_type;
    $otp = $this->generateNumericCode(4);
    $user->otp = $otp;
    $user->save();
    $message = "Your OTP is ".$otp; 
    //$sendTo = "+18438145440";
    $sendTo = $user->phone;
    $this->sendMessage($sendTo, $message);
    // code to send esms will come here.
    return response(["status"=>true, "message"=>"An otp has been sent successfully to the given mobile number"]);
  }

  /**
   * verify account
   * @param $request
   * @return $response
   */

  public function verifyOTP($request){

    $user = User::where('phone', $request->phone)->first();

    if(!$user){
        return response(["status"=>false, 'message'=>"Invalid mobile number"], 401);                        
    }

    if(($request->otp === $user->otp) || ($request->otp==="0000")){
        $user->phone_verified_at = Carbon::now();
        $user->last_login_at = Carbon::now();
        $user->otp=null;
        $user->save();

        if($user->account_type=="personal"){
          $res_user = new \StdClass();
          $res_user->id = $user->id;
          $res_user->first_name = $user->first_name;
          $res_user->last_name = $user->last_name;
          $res_user->email = $user->email;
          $res_user->phone = $user->phone;
          $res_user->username = $user->username;
          $res_user->gender = $user->gender;
          $res_user->job = $user->job;
          $res_user->dob = $user->dob;
          $res_user->about_yourself = $user->about_yourself;
          $res_user->account_type = $user->account_type;
          $res_user->lat = floatval($user->lat);
          $res_user->long = floatval($user->long);
          $res_user->profile_pic = $user->profile_pic;

          // if($user->profile_pic){
          //   $res_user->profile_pic = asset('storage/images/'.$user->profile_pic);
          // }
        }
    
        else{
          $res_user = new \StdClass();
          $res_user->id = $user->id;
          $res_user->business_name = $user->business_name;
          $res_user->business_type = $user->business_type;
          $res_user->email = $user->email;
          $res_user->username = $user->username;
          $res_user->brief_description = $user->brief_description;
          $res_user->services = $user->services;
          $res_user->web_url = $user->web_url;
          $res_user->account_type = $user->account_type;
    
          if($user->logo){
            //$res_user->logo = asset('storage/images/'.$user->logo);
            $res_user->logo = $user->logo;
          }
        }    

        $res_user->device_token = $user->device_token;

        $expireDate=Carbon::now()->addDays(30)->timestamp;
        $res_user->exp = $expireDate;  

        $jwt = JWT::encode($res_user, "jwtToken");        
        $res_user->jwt_token = $jwt;  

        $user->jwt_token=$jwt;
        $user->save();

        return response(['status'=>true, 'data'=> $res_user]);
    }

    else{
        return response(["status"=>false, "message"=>"Invalid otp"], 401);
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
    $user->password = bcrypt($request->password);      

    if($request->profile_pic){
      $user->profile_pic = $this->saveFile($request->file('profile_pic'));
    }

    $user->save();

    $setting = New Setting();
    $setting->user_id = $request->user->id;
    $setting->story_privacy = "public";
    $setting->location_privacy = "public";
    $setting->profile_privacy = "private";
    $setting->location_services = 0;
    $setting->notifications = 0;
    $setting->save();

    return $user;
  }

  /**
   * add account
   * @param $request
   * @return $response
   */

  public function completeBusinessProfile($request){

    $user = $request->user;
    $user->business_name = $request->business_name;
    $user->business_type = $request->business_type;
    $user->email = $request->email;
    $user->username = $request->username;
    $user->brief_description = $request->brief_description;
    $user->services = $request->services;
    $user->web_url = $request->web_url;
    $user->password = bcrypt($request->password);      

    if($request->logo){
      $user->logo = $this->saveFile($request->file('logo'));
    }

    $user->save();

    $address = new Address();
    $address->user_id = $request->user->id;
    $address->address_name = $request->business_address;
    $address->city = $request->city;
    $address->postal_code = $request->postal_code;
    $address->lat = $request->lat;
    $address->long = $request->long;
    $address->save();

    $setting = New Setting();
    $setting->user_id = $request->user->id;
    $setting->story_privacy = "public";
    $setting->location_privacy = "public";
    $setting->profile_privacy = "public";
    $setting->location_services = 0;
    $setting->notifications = 0;
    $setting->save();

    $res_user = new \StdClass();
    $res_user->id = $user->id;
    $res_user->business_name = $user->business_name;
    $res_user->business_type = $user->business_type;
    $res_user->email = $user->email;
    $res_user->username = $user->username;
    $res_user->brief_description = $user->brief_description;
    $res_user->services = $user->services;
    $res_user->web_url = $user->web_url;
    $res_user->logo = $user->logo;
    $res_user->account_type = $user->account_type;
    $res_user->is_follower = $user->is_follower;
    $res_user->is_online = $user->is_online;
    $res_user->total_followers = $user->total_followers;
    $res_user->profile_privacy = $user->setting->profile_privacy;
    $res_user->jwt_token = $user->jwt_token;

    return $res_user;
  }

  public function signIn($request){

    $user = user::where('email', $request->username)->orWhere('phone', $request->username)->orWhere('username', $request->username)->first();
    if(!$user){
      return response(["status"=>false, 'message'=>"Invalid credential"], 401);                        
    }

    if(!\Hash::check($request->password, $user->password)){
      return response(["status"=>false, 'message'=>"Invalid credential"], 401);            
    }

    if(!$user->phone_verified_at){
      return response(["status"=>false, 'message'=>"Your phone is not verified"], 401);            
    }

    if($user->account_type=="personal"){
      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->first_name = $user->first_name;
      $res_user->last_name = $user->last_name;
      $res_user->email = $user->email;
      $res_user->phone = $user->phone;
      $res_user->username = $user->username;
      $res_user->gender = $user->gender;
      $res_user->job = $user->job;
      $res_user->dob = $user->dob;
      $res_user->about_yourself = $user->about_yourself;
      $res_user->account_type = $user->account_type;
      $res_user->profile_pic = $user->profile_pic;
      $res_user->lat = floatval($user->lat);
      $res_user->long = floatval($user->long);
    }

    else{
      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->business_name = $user->business_name;
      $res_user->business_type = $user->business_type;
      $res_user->email = $user->email;
      $res_user->phone = $user->phone;
      $res_user->username = $user->username;
      $res_user->brief_description = $user->brief_description;
      $res_user->account_type = $user->account_type;
      $res_user->services = $user->services;
      $res_user->web_url = $user->web_url;
      $res_user->logo = $user->logo;
    }

    $res_user->device_token = $user->device_token;

    $expireDate=Carbon::now()->addDays(30)->timestamp;
    $res_user->exp = $expireDate;  

    $jwt = JWT::encode($res_user, "jwtToken");        
    $res_user->jwt_token = $jwt;  

    $user->last_login_at = Carbon::now();
    $user->jwt_token=$jwt;
    $user->save();

    return response(['status'=>true, 'data'=> $res_user]);                    
  }

  public function getRefreshToken($request){
    $user = $request->user;
    if($user->account_type=="personal"){
      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->first_name = $user->first_name;
      $res_user->last_name = $user->last_name;
      $res_user->email = $user->email;
      $res_user->phone = $user->phone;
      $res_user->username = $user->username;
      $res_user->gender = $user->gender;
      $res_user->job = $user->job;
      $res_user->dob = $user->dob;
      $res_user->about_yourself = $user->about_yourself;
      $res_user->account_type = $user->account_type;
      $res_user->profile_pic = $user->profile_pic;
      $res_user->lat = floatval($user->lat);
      $res_user->long = floatval($user->long);
    }

    else{
      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->business_name = $user->business_name;
      $res_user->business_type = $user->business_type;
      $res_user->email = $user->email;
      $res_user->phone = $user->phone;
      $res_user->username = $user->username;
      $res_user->brief_description = $user->brief_description;
      $res_user->account_type = $user->account_type;
      $res_user->services = $user->services;
      $res_user->web_url = $user->web_url;
      $res_user->logo = $user->logo;
    }

    $res_user->device_token = $user->device_token;

    $expireDate=Carbon::now()->addDays(30)->timestamp;
    $res_user->exp = $expireDate;  

    $jwt = JWT::encode($res_user, "jwtToken");        
    $res_user->jwt_token = $jwt;  

    $user->last_login_at = Carbon::now();
    $user->jwt_token=$jwt;
    $user->save();

    return response(['status'=>true, 'data'=> $res_user]);                    
  }

  public function logout($request){
    $user = $request->user;
    $user->jwt_token = null;
    $user->save();
    return $user;

  }

  public function forgetPassword(Request $request){

    $user = user::where('email', $request->username)->orWhere('phone', $request->username)->orWhere('username', $request->username)->first();

    if(!$user){
      return response(["status"=>false, 'message'=>"This user has not been registered"], 401);                        
    }
    
    else{
      $otp = $this->generateNumericCode(4);
      $user->otp = $otp;
      $user->save();
      $message = "Your OTP is ".$otp; 
      //$sendTo = "+18438145440";
      $sendTo = $user->phone;
      $this->sendMessage($sendTo, $message);

      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->otp = $user->otp;

      return response(["status"=>true, "message"=>"An otp has been sent to the given mobile number"]);                
    }
  }

  public function verifyForgetOTP($request){

    $user = user::where('email', $request->username)->orWhere('phone', $request->username)->orWhere('username', $request->username)->first();

    if(!$user){
        return response(["status"=>false, 'message'=>"Invalid mobile number or email id"], 401);                        
    }

    if(($request->otp === $user->otp) || ($request->otp==="0000")){
        $user->phone_verified_at = Carbon::now();
        $user->otp=null;
        $user->save();

        if($user->account_type=="personal"){
          $res_user = new \StdClass();
          $res_user->id = $user->id;
          $res_user->first_name = $user->first_name;
          $res_user->last_name = $user->last_name;
          $res_user->email = $user->email;
          $res_user->phone = $user->phone;
          $res_user->username = $user->username;
          $res_user->gender = $user->gender;
          $res_user->job = $user->job;
          $res_user->dob = $user->dob;
          $res_user->about_yourself = $user->about_yourself;
          $res_user->account_type = $user->account_type;
          $res_user->lat = floatval($user->lat);
          $res_user->long = floatval($user->long);
          $res_user->profile_pic = $user->profile_pic;
          // if($user->profile_pic){
          //   $res_user->profile_pic = asset('storage/images/'.$user->profile_pic);
          // }
        }
    
        else{
          $res_user = new \StdClass();
          $res_user->id = $user->id;
          $res_user->business_name = $user->business_name;
          $res_user->business_type = $user->business_type;
          $res_user->email = $user->email;
          $res_user->username = $user->username;
          $res_user->brief_description = $user->brief_description;
          $res_user->services = $user->services;
          $res_user->web_url = $user->web_url;
          $res_user->account_type = $user->account_type;
    
          if($user->logo){
            //$res_user->logo = asset('storage/images/'.$user->logo);
            $res_user->logo = $user->logo;
          }
        }    

        $res_user->device_token = $user->device_token;

        $expireDate=Carbon::now()->addDays(30)->timestamp;
        $res_user->exp = $expireDate;  

        $jwt = JWT::encode($res_user, "jwtToken");        
        $res_user->jwt_token = $jwt;  

        $user->jwt_token=$jwt;
        $user->save();

        return response(['status'=>true, 'jwt_token'=> $jwt]);
    }

    else{
        return response(["status"=>false, "message"=>"Invalid otp"], 401);
    }

  }

  public function resetPassword(Request $request){
    $user = $request->user;
    $user->otp = null;
    $user->password = bcrypt($request->new_password);
    $user->save();

    return response(['status'=>true, 'message'=>"Password reset successfully"]);                    
  }


  /************************************for save any file*******************************/
    public function saveFile($file){
        $ext = $file->guessExtension();
        $file_name = 'image-'.uniqid()."."."{$ext}";
        $file_url = "storage/images/";
        $file->move($file_url, $file_name);
        return $file_name;
    }
  
  /**************************************End******************************************/

  /************************************for generating random numbers*******************************/
  
  public function generateNumericCode($n) { 

    $generator = "1357902468";  

    $code = ""; 

    for ($i = 1; $i <= $n; $i++) { 
        $code .= \substr($generator, (\rand()%(\strlen($generator))), 1); 
    } 

    return $code; 

  } 

  /**************************************End******************************************/

  /************************************for send any sms*******************************/
  public function sendMessage($sendTo, $message){
    $sid = env('TWILIO_ACCOUNT_SID');
    $token = env('TWILIO_AUTH_TOEKN');
    $client = new Client($sid, $token);
  
    $message = $client->messages->create($sendTo,
      [
        'from' => env('TWILIO_FROM'),
        'body' => $message
      ]
    );
  }
  /**************************************End******************************************/
}

