<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\StoryService;
use Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Session;

/*
|=================================================================
| @Class        :   StoryController
| @Description  :   This class is reponsible for all story related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   11-June-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class StoryController extends Controller
{
    protected $storyService;

	public function __construct(StoryService $storyService) {
		$this->storyService = $storyService;
    } 

    /**
     * add story
     * @param Request $request
     * @return $response
    */

    public function addStory(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required',
            'who_can_see'=> 'required|in:public, friends, custom|string|',
            'file' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->storyService->addStory($request);
            if($response){
                return $this->respondWithSuccessMessage("Story added successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid users provided");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

    /**
     * get my stories
     * @param Request $request
     * @return $response
    */

    public function myStories(Request $request){
        try{
            $stories = $this->storyService->myStories($request);
            return $this->respondWithSuccess($stories);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }     
    }

    /**
     * story details
     * @param Request $request
     * @return $response
    */

    public function storyDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $stories = $this->storyService->storyDetails($request);
            return $this->respondWithSuccess($stories);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * delete story
     * @param Request $request
     * @return $response
    */

    public function deleteStory(Request $request){
        $validator = Validator::make($request->all(), [
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->storyService->deleteStory($request);
            if($response){
                return $this->respondWithSuccessMessage("Story deleted successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid id");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

     /**
     * get recent stories
     * @param Request $request
     * @return $response
    */

    public function recentStories(Request $request){
        try{
            $stories = $this->storyService->recentStories($request);
            return $this->respondWithSuccess($stories);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }     
    }

     /**
     * view story
     * @param Request $request
     * @return $response
    */

    public function viewStory(Request $request){
        $validator = Validator::make($request->all(), [
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->storyService->viewStory($request);
            if($response){
                return $this->respondWithSuccessMessage("Story viewed successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid id");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }

      /**
     * like story
     * @param Request $request
     * @return $response
    */

    public function likeStory(Request $request){
        $validator = Validator::make($request->all(), [
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        try{
            $response = $this->storyService->likeStory($request);
            if($response){
                return $this->respondWithSuccessMessage("Story liked successfully");
            }
            else{
                return $this->respondWithSuccessMessage("Invalid id");
            }
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e);
        }   
    }
}