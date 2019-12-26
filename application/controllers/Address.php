<?php

class Address extends MY_Controller {

  public function __construct()
  {
      parent::__construct();
      parent::loadModel('Address_model');
  }

  public function user_get() {
    $client_id = $this->input->get('client_id');
    $result = $this->model->get_many_by('client_id', $client_id);

    $response = array (
      'status' => 'ok',
      'data' => $result,
    );
    return $this->response($response, parent::HTTP_OK);
  }
}