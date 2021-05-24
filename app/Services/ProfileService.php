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
   * locate Me
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
   * add account
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

  public function saveFile($file){
    $ext = $file->guessExtension();
    $file_name = 'image-'.uniqid()."."."{$ext}";
    $file_url = "storage/images/";
    $file->move($file_url, $file_name);
    return $file_name;
}
}
