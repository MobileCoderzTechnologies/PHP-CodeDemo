<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use App\User;
use App\Address;
use App\BusinessType;
use App\LocationInvitation;
use App\Story;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use App\Http\Resources\Business as BusinessResource;
use App\Http\Resources\Personal as PersonalResource;
use DB;

/*
|=================================================================
| @Class        :   StoryService
| @Description  :   This class is reponsible for all story related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   11-June-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class StoryService 
{
    public function addStory($request){
        $story = New Story();
        $story->location_id = $request->location_id;
        $story->user_id = $request->user->id;
        $story->who_can_see = $request->who_can_see;
        $story->manual_location = $request->manual_location;
        $story->file = $this->saveFile($request->file('file'));
        $story->save();

        if($request->who_can_see=="custom"){
            if($request->custom_users){
                $story->customUsers()->attach($request->custom_users);
            }
        }

        if($request->tagged_users){
            $story->taggedUsers()->attach($request->tagged_users);
        }

        return true;
    }

    public function myStories(Request $request){
        return Story::where('user_id', $request->user->id)->where('created_at', '>=', Carbon::now()->subDay())->with(['taggedUsers', 'viewedBy', 'location'])->get();
    }

    public function storyDetails(Request $request){
        return Story::where('user_id', $request->user->id)->where('id', $request->story_id)->with(['taggedUsers', 'viewedBy', 'location'])->first();
    }

    public function deleteStory(Request $request){
        $story = Story::where('user_id', $request->user->id)->where('id', $request->story_id)->first();
        
        if(!$story){
            return false;
        }

        return $story->delete();
    }


    public function saveFile($file){
        $ext = $file->guessExtension();
        $file_name = 'image-'.uniqid()."."."{$ext}";
        $file_url = "storage/images/";
        $file->move($file_url, $file_name);
        return $file_name;
    }
}
