<?php

namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Exception;
use App\User;
use App\Address;
use App\BusinessType;
use App\LocationInvitation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use App\Http\Resources\Business as BusinessResource;
use App\Http\Resources\Personal as PersonalResource;
use DB;

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

    // if($user->profile_pic){
    //     $user->profile_pic = asset('storage/images/'.$user->profile_pic);
    // }

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

  public function getBusinessTypes(){
    return BusinessType::all()->pluck('business_type');
  }

  public function saveFile($file){
    $ext = $file->guessExtension();
    $file_name = 'image-'.uniqid()."."."{$ext}";
    $file_url = "storage/images/";
    $file->move($file_url, $file_name);
    return $file_name;
  }

  public function businessesNearMe(Request $request){
    // $radius = env('NEAR_BY_RADIUS');
    // $business_list = array();
    // $businesses = User::where('account_type', "business")->get();
    // $businesses = BusinessResource::collection($businesses);
    // $latitude = $request->lat;
    // $longitude = $request->long;

    // if(count($businesses) > 0){
    //   $arrDis = [];
    //   foreach ($businesses as $keybusiness => $business) {
    //     $followersId = $business->followers->pluck('id')->toArray();
    //     if($business->lat != null && $business != null )
    //     {
    //       $latFrom = deg2rad($business->lat);
    //       $lonFrom = deg2rad($business->long);
    //       $latTo = deg2rad($latitude);
    //       $lonTo = deg2rad($longitude);

    //       $latDelta = $latTo - $latFrom;
    //       $lonDelta = $lonTo - $lonFrom;

    //       $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    //         cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    //       $distance = ($angle * 6371000) / 1000; // returns distance in kms
    //       $arrDis[] = $distance;

    //       if($distance <= $radius){ 
    //         $business->distance = $distance;
    //         $business->total_followers = $business->followers->count();
    //         unset($business->followers);
    //         array_push($business_list, $business);
    //       }
    //     }
    //   }
    // }
    // //sorting the business based on shortest distance
    // $business_list = array_values(array_sort($business_list, function ($value){
    //    return $value->distance;
    // }));

    // return $business_list;

    $radius = env('NEAR_BY_RADIUS');
    $business_list = array();
    $businesses = Address::all();
    //$businesses = BusinessResource::collection($businesses);
    $latitude = $request->lat;
    $longitude = $request->long;

    if(count($businesses) > 0){
      $arrDis = [];
      foreach ($businesses as $keybusiness => $business) {
        //$followersId = $business->followers->pluck('id')->toArray();
        if($business->lat != null && $business != null )
        {
          $latFrom = deg2rad($business->lat);
          $lonFrom = deg2rad($business->long);
          $latTo = deg2rad($latitude);
          $lonTo = deg2rad($longitude);

          $latDelta = $latTo - $latFrom;
          $lonDelta = $lonTo - $lonFrom;

          $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
          $distance = ($angle * 6371000) / 1000; // returns distance in kms
          $arrDis[] = $distance;

          if($distance <= $radius){ 
            $business->distance = $distance;
            $user = $business->user;
            //$user->total_followers = $user->followers()->wherePivot('status', 'accepted')->count();
            $business->business_details = new BusinessResource($user);
            //$business->total_followers = $business->user->followers->count();
            unset($business->user);
            array_push($business_list, $business);
          }
        }
      }
    }
    //sorting the business based on shortest distance
    $business_list = array_values(array_sort($business_list, function ($value){
       return $value->distance;
    }));

    return $business_list;
  }

  public function followUnfollow($request, $id){
    $ids = [];
    $ids[] = $id;

    if(in_array($id, $request->user->followees->pluck('id')->toArray())){
      $request->user->followees()->detach($ids);
      return "unfollowed";
    }
    else{
      $resUser = [];
      $user = User::where('id', $id)->first();
      $userId = $user->id;
      if($user->setting){
        if($user->setting->profile_privacy == "public"){
          $resUser['status'] = "accepted";
        } 
        else{
          $resUser['status'] = "pending";
        }
      }
      else{
        $resUser['status'] = "pending";
      }

      $request->user->followees()->attach([$userId => $resUser]);
      return "followed";
    }
  }

  public function syncContacts($request){
    $contacts = User::whereIn('phone', $request->contacts)->where('account_type', 'personal')
    ->paginate(20);

    $contacts = PersonalResource::collection($contacts)->response()->getData(true);

    $contactsData = $contacts['data'];
    $resContacts = [];

    foreach($contactsData  as $contact){
      if(!($contact['is_follower'])){
        $resContacts[] = $contact;
      }
    }

    $contacts['data'] = $resContacts;
    return $contacts;
  }

  public function addFriends($request){
    $requestIds = $request->users;
    $users = User::where('account_type', 'personal')->whereIn('id', $requestIds)->get();
    if(count($users) != count($requestIds)){
      return false;
    }

    $newIds = [];
    $attachedIds = $request->user->followees->pluck('id')->toArray();
    foreach($requestIds as $requestId){
      if(!in_array($requestId, $attachedIds)){
        $user = User::where('id', $requestId)->first();
        $resUser = [];
        if($user->setting){
          if($user->setting->profile_privacy == "public"){
            $resUser['status'] = "accepted";
          } 
          else{
            $resUser['status'] = "pending";
          }
        }
        else{
          $resUser['status'] = "pending";
        }
        $request->user->followees()->attach([$requestId => $resUser]);
      }
    }

    return true;
  }

  public function getFriends(Request $request){
    $friends = User::where('account_type', 'personal')
    ->whereHas('followers', function($q) use ($request){
      $q->where('follower_id', $request->user->id)->where('status', 'accepted');
    })
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'accepted');
    })
    ->paginate(20);
    return PersonalResource::collection($friends)->response()->getData(true);
  }

  public function inviteFriends(Request $request){
    foreach($request->users as $user){
      $invitation = new LocationInvitation();
      $invitation->user_id = $user;
      $invitation->invited_by = $request->user->id;
      $invitation->location_type = $request->location_type;
      $invitation->address_name = $request->address_name;
      $invitation->city = $request->city;
      $invitation->postal_code = $request->postal_code;
      $invitation->lat = $request->lat;
      $invitation->long = $request->long;
      $invitation->save();
    }

    return true;

  }

  public function getPlinkdLocations(Request $request){
    $radius = 1;
    $business_list = array();
    $businesses = Address::all();
    $latitude = $request->lat;
    $longitude = $request->long;

    if(count($businesses) > 0){
      $arrDis = [];
      foreach ($businesses as $keybusiness => $business) {
        if($business->lat != null && $business != null )
        {
          $latFrom = deg2rad($business->lat);
          $lonFrom = deg2rad($business->long);
          $latTo = deg2rad($latitude);
          $lonTo = deg2rad($longitude);

          $latDelta = $latTo - $latFrom;
          $lonDelta = $lonTo - $lonFrom;

          $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
          $distance = ($angle * 6371000) / 1000; // returns distance in kms
          $arrDis[] = $distance;

          if($distance <= $radius){ 
            $business->business_name = $business->user->business_name;
            unset($business->user);
            array_push($business_list, $business);
          }
        }
      }
    }
    //sorting the business based on shortest distance
    $business_list = array_values(array_sort($business_list, function ($value){
       return $value->distance;
    }));

    return $business_list;
  }

  public function getallFollowers(Request $request){
    $friends = User::where('account_type', 'personal')
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'accepted');
    })
    ->paginate(20);
    return PersonalResource::collection($friends)->response()->getData(true);
  }

  public function getFollowedBusinesses(Request $request){
    $friends = User::where('account_type', 'business')
    ->whereHas('followers', function($q) use ($request){
      $q->where('follower_id', $request->user->id)->where('status', 'accepted');
    })
    ->paginate(20);
    return BusinessResource::collection($friends)->response()->getData(true);
  }

  public function getFollowerRequests(Request $request){
    $friends = User::where('account_type', 'personal')
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'pending');
    })
    ->paginate(20);
    return PersonalResource::collection($friends)->response()->getData(true);
  }

  /**
   * accept and reject the request
   * @param Request $request
   * @return $response
  */
  public function acceptRejectRequest(Request $request){

    $user = User::where('id', $request->user_id)->first();
    $userId = $user->id;

    if($request->action=="accept"){
      $resUser = [];
      $resUser['status'] = "accepted";
  
      $request->user->followers()->sync([$userId => $resUser], false);
      return "accepted";
    }

    else if($request->action=="reject"){
      $request->user->followers()->detach($userId);
      return "rejected";
    }
  }
}
