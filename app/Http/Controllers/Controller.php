<?php 

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/*
|=================================================================
| @Class        :   Controller
| @Description  :   This is the parent controller class and will be used by all controller classes.
| @Author       :   Arun Kumar Pandey
| @Created_at   :   20-May-2021
| @Modified_at  :   
| @ModifiedBy   :   
|=================================================================
*/

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $statusCode = 200;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }
  
     /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondWithError($message)
    {
        return $this->respond([
            'status' => false,
            'message' => $message
        ]);
    }

       /**
     * @param string $data
     *
     * @return mixed
     */
    public function respondWithSuccess($data)
    {
        return $this->respond([
            'status' => true,
            'data' => $data
        ]);
    }

     /**
     * @param string $message
     *
     * @return mixed
     */

    public function respondWithSuccessMessage($message)
    {
        return $this->respond([
            'status' => true,
            'message' => $message
        ]);
    }
    
     /**
     * @param string $validator
     *
     * @return mixed
     */
    public function respondWithValidationError($validator)
    {
        return $this->setStatusCode(422)->respondWithError($validator->errors()->first());
    }


     /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondNotFound($message = "Not found!")
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

      /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondWithInternalServerError($message = "Internal Server Error")
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }
    
}