<?php

use Restserver\Libraries\REST_Controller;

class Ongkir extends REST_Controller {
  
  function __construct()
  {
      parent::__construct();
      $this->load->library('Rajaongkir');
  }

  public function provinces_get() {
    try {
      $response = $this->rajaongkir->getProvince();
      if($response->code == 200) {
        echo $this->response($response->body->rajaongkir->results, parent::HTTP_OK);
      } else {
        echo json_encode($response);
      }
    } catch(Exception $err) {
      echo($err);
    }
  }

  public function city_get() {
    $province = $this->input->get('province');
    $city = $this->input->get('city');
    $rajaResponse = $this->rajaongkir->getCity($province, $city);
    if($rajaResponse->code == 200) {
      echo $this->response($rajaResponse->body->rajaongkir->results, parent::HTTP_OK);
    } else {
      echo json_encode($rajaResponse);
    }
  }

  public function district_get() {
    echo "[]";
  }

  public function cost_post() {
    $postData = json_decode($this->input->raw_input_stream, true);
    $courier = isset($postData['courier'])? $postData['courier'] : null;
    $rajaResponse = $this->rajaongkir->getCost(
      $postData['origin'],
      $postData['destination'],
      $postData['weight'],
      $courier
    );
    if($rajaResponse->code == 200) {
      echo $this->response($rajaResponse->body->rajaongkir->results, parent::HTTP_OK);
    } else {      
      echo json_encode($rajaResponse);
    }
  }

}