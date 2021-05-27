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

/*
|=================================================================
| @Class        :   ProfileService
| @Description  :   This class is reponsible for all profile related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   24-May-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class ProfileService 
{

    /**
   * s
   * @param $request
   * @return $response
   */

  public function changePassword($request){

    if(!\Hash::check($request->current_password, $request->user->password)){
        return response(["status"=>false, 'message'=>"incorrect current password"], 401);            
    }

    $user = $request->user;
    $user->password = bcrypt($request->new_password);
    $user->save();

    return response(["status"=>true, 'message'=>"password changed successfully"], 200);  
  }

  /**
   * locate me
   * @param $request
   * @return $response
   */

  public function locateMe($request){
    $user = $request->user;  
    $user->lat = $request->lat;
    $user->long = $request->long;
    $user->save();
    if($user->profile_pic){
        $user->profile_pic = asset('storage/images/'.$user->profile_pic);
    }

    return $user;
  }

  /**
   * update profile
   * @param $request
   * @return $response
   */

  public function updateProfile($request){
    $user = $request->user;
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->gender = $request->gender;
    $user->job = $request->job;
    $user->dob = $request->dob;
    $user->about_yourself = $request->about_yourself;    

    if($request->profile_pic){
      $user->profile_pic = $this->saveFile($request->file('profile_pic'));
    }

    $user->save();

    if($user->profile_pic){
        $user->profile_pic = asset('storage/images/'.$user->profile_pic);
    }

    return $user;
  }

   /**
   * update profile
   * @param $request
   * @return $response
   */

  public function updateBusinessProfile($request){

    $user = $request->user;
    $user->business_name = $request->business_name;
    $user->business_type = $request->business_type;
    $user->brief_description = $request->brief_description;
    $user->services = $request->services;
    $user->web_url = $request->web_url;

    if($request->logo){
      $user->logo = $this->saveFile($request->file('logo'));
    }

    $user->save();

    if($user->logo){
      $user->logo = asset('storage/images/'.$user->logo);
    }

    return $user;
  }

  public function addBusinessAddress(Request $request){

    $address = new Address();
    $address->user_id = $request->user->id;
    $address->address_name = $request->address_name;
    $address->city = $request->city;
    $address->postal_code = $request->postal_code;
    $address->lat = $request->lat;
    $address->long = $request->long;
    $address->save();

    return $address;

  }

  public function updateBusinessAddress(Request $request){

    $address = Address::where('id', $request->address_id)->first();

    if($address && $address->user_id==$request->user->id){
      $address->user_id = $request->user->id;
      $address->address_name = $request->address_name;
      $address->city = $request->city;
      $address->postal_code = $request->postal_code;
      $address->lat = $request->lat;
      $address->long = $request->long;
      $address->save();
    }

    else{
      return false;
    }

    return true;
  }

  public function deleteBusinessAddress(Request $request){
    $address = Address::where('id', $request->address_id)->first();

    if($address && $address->user_id==$request->user->id){
      $address->delete();
    }

    else{
      return false;
    }

    return true;
  }

  public function saveFile($file){
    $ext = $file->guessExtension();
    $file_name = 'image-'.uniqid()."."."{$ext}";
    $file_url = "storage/images/";
    $file->move($file_url, $file_name);
    return $file_name;
  }
}