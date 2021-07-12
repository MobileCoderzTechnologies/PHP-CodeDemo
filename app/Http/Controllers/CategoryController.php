<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Session;

/*
|=================================================================
| @Class        :   CategoryController
| @Description  :   This class is reponsible for all business category related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   12-July-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class CategoryController extends Controller
{
    protected $categoryService;
	public function __construct(CategoryService $categoryService) {
		$this->categoryService = $categoryService;
    } 


    /**
     * category list
     * @param Request $request
     * @return $response
     */
    
    public function categories(Request $request){
        
        try{
            $response = $this->categoryService->categories($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }

     /**
     * add categories
     * @param Request $request
     * @return $response
     */
    
    public function addCategories(Request $request){

        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',  
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        
        try{
            $response = $this->categoryService->addCategories($request);
            return $this->respondWithSuccessMessage("Category added successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }

    /**
     * add employee
     * @param Request $request
     * @return $response
     */
    
    public function addEmployee(Request $request){

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',  
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        
        try{
            $response = $this->categoryService->addEmployee($request);
            return $this->respondWithSuccessMessage("Employee added successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }

     /**
     * add future event
     * @param Request $request
     * @return $response
     */
    
    public function futureEvent(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',  
            'date' => 'required',
            'time' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }
        
        try{
            $response = $this->categoryService->futureEvent($request);
            return $this->respondWithSuccessMessage("Future event added successfully");
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }

    /**
     * employees
     * @param Request $request
     * @return $response
     */
    
    public function employees(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'business_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->categoryService->employees($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }

    /**
     * future events
     * @param Request $request
     * @return $response
     */
    
    public function events(Request $request){

         $validator = Validator::make($request->all(), [
            'business_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            return $this->respondWithValidationError($validator);
        }

        try{
            $response = $this->categoryService->events($request);
            return $this->respondWithSuccess($response);
        }
        catch(Exception $e){
            return $this->respondWithInternalServerError($e->getMessage());
        }
    }
}