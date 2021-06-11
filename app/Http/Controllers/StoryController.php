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
            'location_id' => 'required',
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
}