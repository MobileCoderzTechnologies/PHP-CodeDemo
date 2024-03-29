<?php

namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Exception;
use App\User;
use App\Story;
use App\Address;
use App\Setting;
use App\BusinessType;
use App\LocationInvitation;
use App\Chat;
use App\ChatParticipant;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use App\Http\Resources\Business as BusinessResource;
use App\Http\Resources\Personal as PersonalResource;
use App\Http\Resources\User as UserResource;
use DB;
use App\Review;
use App\Report;
use App\Notification;
use Log;

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
        return response(["status"=>false, 'message'=>"incorrect current password"], 403);            
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

    $user->lat = floatval($user->lat);
    $user->long = floatval($user->long);

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

    // if($user->logo){
    //   $user->logo = asset('storage/images/'.$user->logo);
    // }

    $user->lat = floatval($user->lat);
    $user->long = floatval($user->long);

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
      return 0;
    }

    return 1;
  }

  public function makeAddressPrimary(Request $request){
    
    $address = Address::where('id', $request->address_id)->first();
    if($address && $address->user_id==$request->user->id){
      $primary = Address::where('user_id', $request->user->id)->where('is_primary', 1)->first();
      if($primary){
        $primary->is_primary = 0;
        $primary->save();
      }
      $address->is_primary = 1;
      $address->save();
    }

    else{
      return 0;
    }

    return 1;
  }

  public function deleteBusinessAddress(Request $request){
    $address = Address::where('id', $request->address_id)->first();

    if($address && $address->user_id==$request->user->id){
      $address->delete();
    }

    else{
      return 0;
    }

    return 1;
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
    $radius = env('NEAR_BY_RADIUS');
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
            $business->distance = $distance;
            $user = $business->user;
            $business->business_details = new BusinessResource($user);
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

  public function onOffStoryNotifications($request){
    if(in_array($request->user_id, $request->user->notifyBy->pluck('id')->toArray())){
      $request->user->notifyBy()->detach($request->user_id);
      return "turned off";
    }
    else{
      $request->user->notifyBy()->attach($request->user_id);
      return "turned on";
    }
  }

  public function syncContacts($request){
    if($request->name){
      $nameArray = explode(" ", $request->name);
      if(count($nameArray)==1){
        $contacts = User::where('id', "!=", $request->user->id)->whereIn('phone', $request->contacts)->where('account_type', 'personal')
        ->where('first_name', 'like', '%' . $request->name . '%')
        ->orWhere('last_name', 'like', '%' . $request->name . '%')
        ->paginate(20);
      }
  
      else if(count($nameArray)==2){
        $first_name = $nameArray[0];
        $last_name = $nameArray[1];
        $contacts = User::where('id', "!=", $request->user->id)->whereIn('phone', $request->contacts)->where('account_type', 'personal')
        ->where('first_name', 'like', '%' . $first_name . '%')
        ->where('last_name', 'like', '%' . $last_name . '%')
        ->paginate(20);
      }
    }

    else{
      $contacts = User::where('id', "!=", $request->user->id)->whereIn('phone', $request->contacts)->where('account_type', 'personal')
      ->paginate(20);
    }

    $contacts = UserResource::collection($contacts)->response()->getData(true);

    $contactsData = $contacts['data'];
    $resContacts = [];

    foreach($contactsData  as $contact){
      if($contact['is_follower']=="no"){
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
      return 0;
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
        if($resUser['status'] == "pending"){
          /*****************************Notification*****************************************************/
          $notifiedUser = $user;
          if($notifiedUser->setting && $notifiedUser->setting->notifications){
            $notification = new Notification();
            $notification->user_id = $notifiedUser->id;
            $notification->title = "New Friend Request";
            $notification->type = "Friend Request";
            $notification->message = $request->user->first_name." ".$request->user->last_name." sent you friend request";
            $notification->requested_by = $request->user->id;
            $notification->save();
      
            $user = $notifiedUser;
            $notification_id = $user->device_token;
            $title = $notification->title;
            $message = $notification->message;
            $id = $user->id;
            $type = $notification->type;
            
            $res = send_notification_FCM($notification_id, $title, $message, $id,$type);
          
            if($res == 1){
              Log::info('Notification sent');
          
            }else{
          
              Log::error('Error on sending notification');
            }
          }
          /***********************************End***********************************************************/
        }
      }
    }

    return 1;
  }

  public function getFriends(Request $request){
    if($request->name){
      $nameArray = explode(" ", $request->name);
      if(count($nameArray)==1){
        $friends = User::where('first_name', 'like', '%' . $request->name . '%')
        ->orWhere('last_name', 'like', '%' . $request->name . '%')->orWhere('business_name', 'like', '%' . $request->name . '%')
        ->whereHas('followers', function($q) use ($request){
          $q->where('follower_id', $request->user->id)->where('status', 'accepted');
        })
        ->whereHas('followees', function($q) use ($request){
          $q->where('followee_id', $request->user->id)->where('status', 'accepted');
        })
        ->paginate(20);
      }
      else if(count($nameArray)>1){
        $first_name = $nameArray[0];
        $last_name = $nameArray[1];
        $friends = User::where('first_name', 'like', '%' . $first_name . '%')
        ->where('last_name', 'like', '%' . $last_name . '%')->orWhere('business_name', 'like', '%' . $request->name . '%')
        ->whereHas('followers', function($q) use ($request){
          $q->where('follower_id', $request->user->id)->where('status', 'accepted');
        })
        ->whereHas('followees', function($q) use ($request){
          $q->where('followee_id', $request->user->id)->where('status', 'accepted');
        })
        ->paginate(20);
      }
    }
    else{
      $friends = User::whereHas('followers', function($q) use ($request){
        $q->where('follower_id', $request->user->id)->where('status', 'accepted');
      })
      ->whereHas('followees', function($q) use ($request){
        $q->where('followee_id', $request->user->id)->where('status', 'accepted');
      })
      ->paginate(20);
    }

    return UserResource::collection($friends)->response()->getData(true);
  }

  public function searchUser(Request $request){
    if($request->name){
      $nameArray = explode(" ", $request->name);
      if(count($nameArray)==1){
        $friends = User::where('first_name', 'like', '%' . $request->name . '%')
        ->orWhere('last_name', 'like', '%' . $request->name . '%')->orWhere('business_name', 'like', '%' . $request->name . '%')
        ->paginate(20);
      }
      else if(count($nameArray) > 1){
        $first_name = $nameArray[0];
        $last_name = $nameArray[1];
        $friends = User::where('first_name', 'like', '%' . $first_name . '%')
        ->where('last_name', 'like', '%' . $last_name . '%')->orWhere('business_name', 'like', '%' . $request->name . '%')
        ->paginate(20);
      }

      return UserResource::collection($friends)->response()->getData(true);
    }
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

      $indexId = "";

      if ($user > $request->user->id) {
        $indexId = $request->user->id.",".$user;
      }
      else {
        $indexId = $user.",".$request->user->id;
      }
      
      $chat = new Chat();
      $chat->senderId = $request->user->id;
      $chat->receiverId = $user;
      $chat->indexId = $indexId;
      $chat->invitation_id = $invitation->id;
      $chat->location_type = $request->location_type;
      $chat->address_name = $request->address_name;
      $chat->city = $request->city;
      $chat->postal_code = $request->postal_code;
      $chat->lat = $request->lat;
      $chat->long = $request->long;
      $chat->save();
      $chatParticipant = ChatParticipant::where('indexId', $indexId)->first();

      if(!$chatParticipant){
        $chatParticipant = new ChatParticipant();
      }

      $chatParticipant->chatId = $chat->id;
      $chatParticipant->senderId = $request->user->id;
      $chatParticipant->receiverId = $user;
      $chatParticipant->indexId = $indexId;
      $chatParticipant->save();


      /*****************************Notification*****************************************************/
      $notifiedUser = User::where('id', $user)->first();
      if($notifiedUser->setting && $notifiedUser->setting->notifications){
        $notification = new Notification();
        $notification->user_id = $user;
        $notification->title = "New Invitation";
        $notification->type = "Invitation";
        $notification->message = $request->user->first_name." ".$request->user->last_name." sent you invitation";
        $notification->requested_by = $request->user->id;
        $notification->save();
  
        $user = User::where('id', $user)->first();
        $notification_id = $user->device_token;
        $title = $notification->title;
        $message = $notification->message;
        $id = $user->id;
        $type = $notification->type;
        
        $res = send_notification_FCM($notification_id, $title, $message, $id,$type);
      
        if($res == 1){
          Log::info('Notification sent');
      
        }else{
      
          Log::error('Error on sending notification');
        }
      }
      /***********************************End***********************************************************/

    }

    return 1;

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
    $friends = User::whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'accepted');
    })
    ->paginate(20);
    return UserResource::collection($friends)->response()->getData(true);
  }

  public function discoverList(Request $request){
    if($request->name){
      $nameArray = explode(" ", $request->name);
      if(count($nameArray)==1){
        $discovers = $request->user->followees()->where('first_name', 'like', '%' . $request->name . '%')
        ->orWhere('last_name', 'like', '%' . $request->name . '%')
        ->paginate(20);
      }
  
      else if(count($nameArray)==2){
        $first_name = $nameArray[0];
        $last_name = $nameArray[1];
        $discovers = $request->user->followees()->where('first_name', 'like', '%' . $first_name . '%')
        ->where('last_name', 'like', '%' . $last_name . '%')
        ->paginate(20);
      }
    }
    else{
      $discovers = $request->user->followees()
      ->paginate(20);
    }

    return PersonalResource::collection($discovers)->response()->getData(true);
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
    $friends = User::whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'pending');
    })
    ->paginate(20);
    return UserResource::collection($friends)->response()->getData(true);
  }

  public function acceptRejectRequest(Request $request){

    $user = User::where('id', $request->user_id)->first();
    $userId = $user->id;

    if($request->action=="accept"){
      $resUser = [];
      $resUser['status'] = "accepted";
  
      $request->user->followers()->sync([$userId => $resUser], false);

      /*****************************Notification*****************************************************/
      if($user->setting && $user->setting->notifications){
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->title = "Request Accepted";
        $notification->type = "Friend Request";
        $notification->message = $request->user->first_name." ".$request->user->last_name." has accepted your request";
        $notification->requested_by = $user->id;
        $notification->accepted_by = $request->user->id;
        $notification->save();
  
        $notification_id = $user->device_token;
        $title = $notification->title;
        $message = $notification->message;
        $id = $user->id;
        $type = $notification->type;
        
        $res = send_notification_FCM($notification_id, $title, $message, $id,$type);
      
        if($res == 1){
          Log::info('Notification sent');
      
        }else{
      
          Log::error('Error on sending notification');
        }
      }
      /***********************************End***********************************************************/

      return "accepted";
    }

    else if($request->action=="reject"){
      $request->user->followers()->detach($userId);
      return "rejected";
    }
  }

  
  public function changeStoryPrivacy(Request $request){
   $setting = Setting::where('user_id', $request->user->id)->first();
   $setting->story_privacy = $request->status;
   $setting->save();
   return true;
  }

  public function changeLocationPrivacy(Request $request){
    $setting = Setting::where('user_id', $request->user->id)->first();
    $setting->location_privacy = $request->status;
    $setting->save();
    return true;
  }

  public function changeProfilePrivacy(Request $request){
    $setting = Setting::where('user_id', $request->user->id)->first();
    $setting->profile_privacy = $request->status;
    $setting->save();
    return true;
  }

  public function onOffLocationService(Request $request){
    $setting = Setting::where('user_id', $request->user->id)->first();
    $setting->location_services	 =  !($setting->location_services);
    $setting->save();
    return true;
  }

  public function onOffNotifications(Request $request){
    $setting = Setting::where('user_id', $request->user->id)->first();
    $setting->notifications	 =  !($setting->notifications);
    $setting->save();
    return true;
  }

  public function checkBlockedMe(Request $request){
    return User::where('id', $request->user_id)->select('id')->first();
  }

  public function getProfile(Request $request){
    
    $user = User::where('id', $request->user_id)->first();
    $res_user = null;

    if(!$user){
      return false;
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
      $res_user->lat = floatval($user->lat);
      $res_user->long = floatval($user->long);
      $res_user->profile_pic = $user->profile_pic;
      $res_user->profile_privacy = $user->setting->profile_privacy;
      $res_user->is_follower = $user->is_follower;
      $res_user->is_blocked = $user->is_blocked;
      $res_user->blocked_me = $user->blocked_me;
      $res_user->is_online = $user->is_online;
      $res_user->recent_stories_count = $user->recentStories->count();
      $res_user->recent_stories = $user->recentStories;
      
    } 

    else{
      $res_user = new \StdClass();
      $res_user->id = $user->id;
      $res_user->first_name = $user->first_name;
      $res_user->last_name = $user->last_name;
      $res_user->profile_pic = $user->profile_pic;
      $res_user->business_name = $user->business_name;
      $res_user->business_type = $user->business_type;
      $res_user->email = $user->email;
      $res_user->username = $user->username;
      $res_user->brief_description = $user->brief_description;
      $res_user->services = $user->services;
      $res_user->web_url = $user->web_url;
      $res_user->account_type = $user->account_type;
      $res_user->logo = $user->logo;
      $res_user->profile_privacy = $user->setting->profile_privacy;
      $res_user->is_follower = $user->is_follower;
      $res_user->is_blocked = $user->is_blocked;
      $res_user->blocked_me = $user->blocked_me;
      $res_user->is_online = $user->is_online;
      $res_user->recent_stories_count = $user->recentStories->count();
      $res_user->recent_stories = $user->recentStories;
    }    

    $followers = User::where('account_type', 'personal')
      ->whereHas('followees', function($q) use ($user){
      $q->where('followee_id', $user->id)->where('status', 'accepted');
    })
    ->limit(5)->get();
    $followers = PersonalResource::collection($followers)->response()->getData(true);
    $lats = DB::table('stories')
    ->where('user_id', $user->id)
    ->select(DB::raw('lat'), DB::raw('count(*) as count'))
    ->groupBy('lat', 'long')
    ->orderBy('count', 'desc')
    ->take(5)
    ->pluck('lat')->toArray();
    $top_places = DB::table('stories')->where('user_id', $user->id)->whereIn('lat', $lats)->select(['business_name', 'lat', 'long', 'business_image', 'business_id'])->groupBy('lat', 'long')->take(5)->get();
    $recent_places = DB::table('stories')->where('user_id', $user->id)->orderBy('id', 'DESC')->groupBy('lat', 'long')->select(['business_name', 'lat', 'long', 'business_image', 'business_id'])->take(5)->get();
    $plinkds = Story::where('user_id', $user->id)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->limit(5)->get();

    $plinkd_count = Story::where('user_id', $user->id)->count();
    //$followers_count = count($followers);
    $followers_count = User::where('account_type', 'personal')
    ->whereHas('followees', function($q) use ($user){
    $q->where('followee_id', $user->id)->where('status', 'accepted');
  })
  ->count();

    if(in_array($user->id, $request->user->notifyBy->pluck('id')->toArray())){
      $res_user->story_notifications = 1;
    }

    else{
      $res_user->story_notifications = 0;
    }

    $res_user->followers = $followers;
    $res_user->top_places = $top_places;
    $res_user->recent_places = $recent_places;
    $res_user->plinkds = $plinkds;
    $res_user->top_place_count = count($top_places);
    $res_user->recent_place_count = count($recent_places);
    $res_user->plinkd_count = $plinkd_count;
    $res_user->followers_count = $followers_count;

    return $res_user;
  }

  public function topPlaces(Request $request){
    $user = User::where('id', $request->user_id)->first();
    if(!$user){
      return false;
    }
    return DB::table('stories')->where('user_id', $user->id)->groupBy('lat', 'long')->select(['business_name', 'lat', 'long', 'business_image', 'business_id'])->paginate(20);
  }

  
  public function plinkds(Request $request){
    $user = User::where('id', $request->user_id)->first();
    if(!$user){
      return false;
    }
    return Story::where('user_id', $user->id)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->paginate(20);
  }

  
  public function recentPlaces(Request $request){
    $user = User::where('id', $request->user_id)->first();
    if(!$user){
      return false;
    }
    return DB::table('stories')->where('user_id', $user->id)->groupBy('lat', 'long')->select(['business_name', 'lat', 'long', 'business_image', 'business_id'])->paginate(20);
  }

  public function provideReview(Request $request){
    $review = Review::where('business_id', $request->business_id)->where('reviewed_by', $request->user->id)->first();
    if(!$review){
      $review = New Review();
    }

    $review->business_id = $request->business_id;
    $review->reviewed_by = $request->user->id;
    $review->video = $this->saveFile($request->file('video')); 
    $review->save();

    return $review;
  }

  public function recentlyAddedFriends(Request $request){
    $friends = User::where('account_type', 'personal')
    ->whereHas('followers', function($q) use ($request){
      $q->where('follower_id', $request->user->id)->where('status', 'accepted');
    })
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'accepted');
    })
    ->limit(20)->get();
    return PersonalResource::collection($friends)->response()->getData(true);
  }

  public function userStoriesOnMybusiness(Request $request){
    $user = $request->user;
    $lats = $user->addresses->pluck('lat')->toArray();
    $longs = $user->addresses->pluck('long')->toArray();
    return Story::where('user_id', $request->user_id)->whereIn('lat', $lats)->whereIn('long', $longs)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->paginate(20);
  }

  public function getBusinessProfile(Request $request){
    
    $user = User::where('id', $request->business_id)->where('account_type', 'business')->first();
    $res_user = null;
    
    if(!$user){
      return false;
    }
    
    $lats = $user->addresses->pluck('lat')->toArray();
    $longs = $user->addresses->pluck('long')->toArray();

    $res_user = new \StdClass();
    $res_user->id = $user->id;
    $res_user->business_name = $user->business_name;
    $res_user->business_type = $user->business_type;
    $res_user->email = $user->email;
    $res_user->phone = $user->phone; 
    $res_user->username = $user->username;
    $res_user->brief_description = $user->brief_description;
    $res_user->services = $user->services;
    $res_user->web_url = $user->web_url;
    $res_user->account_type = $user->account_type;
    $res_user->logo = $user->logo;
    if($user->setting){
      $res_user->profile_privacy = $user->setting->profile_privacy;
    }
    else{
      $res_user->profile_privacy = "public";
    }
    $res_user->is_follower = $user->is_follower;  
    $res_user->is_blocked = $user->is_blocked;
    $res_user->blocked_me = $user->blocked_me;
    $res_user->is_online = $user->is_online;  
    $res_user->recent_stories_count = $user->recentStories->count();
    $res_user->recent_stories = $user->recentStories;

    $followers = User::where('account_type', 'personal')
      ->whereHas('followees', function($q) use ($user){
      $q->where('followee_id', $user->id)->where('status', 'accepted');
    })
    ->limit(5)->get();
    $followers = PersonalResource::collection($followers)->response()->getData(true);
    $plinkds_by_business = Story::where('user_id', $user->id)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->limit(5)->get();
    $plinkds_on_business = Story::whereIn('lat', $lats)->whereIn('long', $longs)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->limit(5)->get();
    $reviews = Review::where('business_id', $user->id)->with('reviewedBy')->limit(5)->get();
    $plinkd_by_business_count = Story::where('user_id', $user->id)->count();
    $plinkd_on_business_count = Story::whereIn('lat', $lats)->whereIn('long', $longs)->count();
    $followers_count = User::where('account_type', 'personal')
      ->whereHas('followees', function($q) use ($user){
      $q->where('followee_id', $user->id)->where('status', 'accepted');
    })
    ->count();
    
    if(in_array($user->id, $request->user->notifyBy->pluck('id')->toArray())){
      $res_user->story_notifications = 1;
    }

    else{
      $res_user->story_notifications = 0;
    }

    $reviews_count = Review::where('business_id', $user->id)->count();
    $res_user->followers = $followers;
    $res_user->plinkds_by_business = $plinkds_by_business;
    $res_user->plinkds_on_business = $plinkds_on_business;
    $res_user->reviews = $reviews;
    $res_user->plinkd_by_business_count = $plinkd_by_business_count;
    $res_user->plinkd_on_business_count = $plinkd_on_business_count;
    $res_user->reviews_count = $reviews_count;
    $res_user->followers_count = $followers_count;
    $res_user->categories = $user->categories;
    $res_user->business_addresses = $user->addresses;

    return $res_user;
  }

  public function plinkdsByBusiness(Request $request){
    $user = User::where('id', $request->business_id)->where('account_type', 'business')->first();
    $res_user = null;
    
    if(!$user){
      return false;
    }

    return Story::where('user_id', $user->id)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->paginate(20);
    
  }

  public function plinkdsOnBusiness(Request $request){
    $user = User::where('id', $request->business_id)->where('account_type', 'business')->first();
    $res_user = null;
    
    if(!$user){
      return false;
    }
    
    $lats = $user->addresses->pluck('lat')->toArray();
    $longs = $user->addresses->pluck('long')->toArray();

    return Story::whereIn('lat', $lats)->whereIn('long', $longs)->orderBy('id', 'desc')->select(['id', 'user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])->paginate(20);
    //->select(['user_id', 'file', 'business_name', 'lat', 'long', 'business_image', 'business_id', 'file', 'created_at'])->with(['storyAddedBy', 'taggedUsers'])

  }


  public function reviews(Request $request){
    $user = User::where('id', $request->business_id)->where('account_type', 'business')->first();
    $res_user = null;
    
    if(!$user){
      return false;
    }
    
    return Review::where('business_id', $user->id)->with('reviewedBy')->paginate(20);
  }

  public function totalPlinkdFriends(Request $request){
    $friends = User::where('account_type', 'personal')
    ->whereHas('followers', function($q) use ($request){
      $q->where('follower_id', $request->user_id)->where('status', 'accepted');
    })
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user_id)->where('status', 'accepted');
    })
    ->count();

    return $friends;
  }

  public function reportUser(Request $request){
    
    $report = Report::where('reported_by', $request->user->id)->where('reported_to', $request->user_id)->first();

    if(!$report){
      $report = new Report();
    }

    $report->reported_by = $request->user->id;
    $report->reported_to = $request->user_id;
    $report->report_message = $request->report_message;
    $report->save();

    return $report;
  }

  public function blockUser($request){
    $user = $request->user;
    $user->blockedTo()->sync($request->user_id, false);
  }

  public function unblockUser($request){
    $user = $request->user;
    $user->blockedTo()->detach($request->user_id);
  }

  
  public function removeFromFollowers($request){
     $user = $request->user;
     $user->followers()->detach($request->user_id);
  }

  public function acceptRejectInvitation($request){
    $invitation = LocationInvitation::where('id', $request->invitation_id)->where('user_id', $request->user->id)->first();
    $chat = Chat::where('invitation_id', $request->invitation_id)->where('receiverId', $request->user->id)->first();

    if(!$invitation){
      return 0;
    }

    if($chat){
      $chat->invitation_status = $request->status;
      $chat->save();
    }

    $invitation->status = $request->status;
    $invitation->save();

    /*****************************Notification*****************************************************/
    $notifiedUser = User::where('id', $invitation->invited_by)->first();
    if($notifiedUser->setting && $notifiedUser->setting->notifications){
      $notification = new Notification();
      $notification->user_id = $invitation->invited_by;
      $notification->title = "Invitation Accepted";
      $notification->type = "Invitation";
      $notification->message = $request->user->first_name." ".$request->user->last_name." has accepted the invitation";
      $notification->requested_by = $request->user->id;
      $notification->save();
  
      $user = $notifiedUser;
      $notification_id = $user->device_token;
      $title = $notification->title;
      $message = $notification->message;
      $id = $user->id;
      $type = $notification->type;
      
      $res = send_notification_FCM($notification_id, $title, $message, $id,$type);
    
      if($res == 1){
        Log::info('Notification sent');
    
      }else{
    
        Log::error('Error on sending notification');
      }
    }

    /***********************************End***********************************************************/


    return $invitation;
  }

  public function markNotificationAsRead($request){
    if($request->notification_id){
      $notification = Notification::where('id', $request->notification_id)->first();
      $notification->is_read = 1;
      $notification->save();
      return $notification;
    }

    else{
      $request->user->unreadNotifications()->update(array('is_read' => 1));
    }
  }

  public function getNotifications($request){
    return Notification::where('user_id', $request->user->id)->orderBy('id', 'DESC')->with(['requestedBy', 'acceptedBy'])->get();
  }

  public function markAsOnline(Request $request){
    $user = $request->user;
    $user->is_online = 1;
    $user->save();
    return $user; 
  }

  public function markAsOffline(Request $request){
    $user = $request->user;
    $user->is_online = 0;
    $user->save();
    return $user; 
  }

  public function getOnlineFriends(Request $request){
    $friends = User::where('account_type', 'personal')
    ->where('is_online', 1)
    ->whereHas('followers', function($q) use ($request){
      $q->where('follower_id', $request->user->id)->where('status', 'accepted');
    })
    ->whereHas('followees', function($q) use ($request){
      $q->where('followee_id', $request->user->id)->where('status', 'accepted');
    })
    ->paginate(20);
    return PersonalResource::collection($friends)->response()->getData(true);
  }

  public function getSettingDetail(Request $request){
    $setting = $request->user->setting;
    $setting->story_count = $request->user->recentStories->count();
    return $setting;
  }
}
