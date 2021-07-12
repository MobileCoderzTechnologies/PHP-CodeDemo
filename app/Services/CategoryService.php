<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use App\User;
use App\Category;
use App\Employee;
use App\FutureEvent;

/*
|=================================================================
| @Class        :   CategoryService
| @Description  :   This class is reponsible for all business category related tasks.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   12-July-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class CategoryService 
{
  public function categories(Request $request){
    return Category::all();
  }

  public function addCategories(Request $request){
    $request->user->categories()->sync($request->categories);
    return true;
  }
    
  public function addEmployee($request){
    $employee = new Employee();
    $employee->name = $request->name;
    $employee->user_id = $request->user->id;
    $employee->category_id = $request->category_id;
    $employee->phone_number = $request->phone_number;
    $employee->email = $request->email;
    $employee->description = $request->description;

    if($request->photo){
      $employee->photo = $this->saveFile($request->file('photo')); 
    }

    $employee->save();

    return true;
  }
  
  public function futureEvent($request){
    $event = new FutureEvent();
    $event->user_id = $request->user->id;
    $event->name = $request->name;
    $event->date = $request->date;
    $event->time = $request->time;
    $event->description = $request->description;

    if($request->photo){
      $event->photo = $this->saveFile($request->file('photo')); 
    }

    $event->save();
    return true;
  }
  
  public function employees($request){
    return Employee::where('user_id', $request->business_id)->where('category_id', $request->category_id)->orderBy('id', 'DESC')->paginate(20);
  }
  
  public function events(Request $request){
    return FutureEvent::where('user_id', $request->business_id)->orderBy('id', 'DESC')->paginate(20);
  }

  public function saveFile($file){
    $ext = $file->guessExtension();
    $file_name = 'image-'.uniqid()."."."{$ext}";
    $file_url = "storage/images/";
    $file->move($file_url, $file_name);
    return $file_name;
  }
}