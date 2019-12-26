<?php
class Cart extends MY_Controller {
  public function __construct()
    {
        parent::__construct();
        parent::loadModel('Cart_model');
    }

  public function add_to_cart_post() {
    $postData = json_decode($this->input->raw_input_stream, true);
    if(!isset($postData['itemId']) || !isset($postData['clientId'])) {
      return $this->response(array(
        'status'=> 'error',
        'message' => 'Please provide clientId and or itemId'
      ), parent::HTTP_BAD_REQUEST);
    }
    $cartData = array(
      'item_id' => $postData['itemId'],
      'client_id' => $postData['clientId'],
      'qty' => $postData['qty'],
      'notes' => $postData['notes'],
      'created_date' => time(), 
    );
    $savedData = $this->model->insert($cartData);
    $response = array(
      'status' => 'ok',
      'data' => $savedData
    );
    return $this->response($response, parent::HTTP_ACCEPTED);
  }
  public function user_cart_get() {
    $clientId = $this->input->get('clientId');
    if(!isset($clientId)) {
      return $this->response(null, parent::HTTP_NOT_FOUND);
    }
    $data = $this->model->get_many_by('client_id', $clientId);
    $response = array(
      'status' => 'ok',
      'data' => $data
    );
    return $this->response($response, parent::HTTP_OK);
  }

  public function remove_delete() {
    $cartId = $this->input->get('cartId');
    if(!isset($cartId)) {
      return $this->response(null, parent::HTTP_BAD_REQUEST);
    }
    $deleted = $this->model->delete($cartId);
    $response = array(
      'status' => $deleted
    );
    return $this->response($response, parent::HTTP_ACCEPTED);
  }
}

?>