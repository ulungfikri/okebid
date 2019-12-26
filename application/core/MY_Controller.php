<?php 

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class MY_Controller extends REST_Controller {


  public function __construct() {
    parent::__construct();
    $this->load->library('user_agent');
  }

  public function loadModel($modelName) {
    $this->load->model($modelName, 'model');
  }

  function all_get()
    {   
        $ara= $this->model->get_all();
        $response = array(
            'status'=> 'ok',
            'result' => $ara
        );
        $this->response($response, parent::HTTP_OK);
    }

  function index_get($id=-1) {
    if($id== -1) {
      return $this->response(array("error" => "Missing ID"), parent::HTTP_BAD_REQUEST);
    }
    $data = $this->model->get($id);
    $response = array(
      'status'=> 'ok',
      'data' => $data
    );    
    if (is_null($data)) {
      return $this->response(null, parent::HTTP_NOT_FOUND);;
    }
    return $this->response($response, parent::HTTP_OK);
  }

  function index_delete($id=-1) {
    if($id== -1) {
      return $this->response(array("error" => "Missing ID"), parent::HTTP_BAD_REQUEST);
    }
    $data = $this->model->delete($id);
    $response = array(
      'status'=> 'ok',
      'data' => $data
    );
    $this->response($response, parent::HTTP_OK);
  }

  function index_put($id=-1) {
    if($id== -1) {
      return $this->response(array("error" => "Missing ID"), parent::HTTP_BAD_REQUEST);
    }
    $postData = json_decode($this->input->raw_input_stream, true);
    $status = $this->model->update($id, $postData, true);
    if ($status) {
      $data = $this->model->get($id);
      $response = array(
        'status' => 'ok',
        'data' => $data
      );
      $this->response($response, parent::HTTP_OK);
    } else {
      return $this->response(null, parent::HTTP_BAD_REQUEST);
    }    
  }

  function index_post() {
    $postData = json_decode($this->input->raw_input_stream, true);
    $insertId = $this->model->insert($postData);
    if (isset($insertId)) {
      $data = $this->model->get($insertId);
      $response = array(
        'status' => 'ok',
        'data' => $data
      );
      return $this->response($response, parent::HTTP_OK);
    } else {
      return $this->response(null, parent::HTTP_BAD_REQUEST);
    }
    
  }

  protected function needAuth() {
    $this->verify_request();
  }

  private function verify_request()
    {
        $headers = $this->input->request_headers();
        if (!isset($headers['Authorization'])) {
          $this->unauthorized();
        }
        $token = $headers['Authorization'];
        try {
            
            $data = validateToken($token);
            if ($data === false) {
              $this->unauthorized();
            } else {
                return TRUE;
            }
        } catch (Exception $e) {
          $this->unauthorized();
        }
    }
    private function unauthorized() {
      $status = parent::HTTP_UNAUTHORIZED;
      $response = array('status' => $status, 'msg' => 'Unauthorized Access! ');
      $this->response($response, $status);
    }

}


?>