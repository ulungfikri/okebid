<?php
use Restserver\Libraries\REST_Controller;

class Wishlist extends REST_Controller {

    function __construct()
    {
      parent::__construct();
      $this->load->model('Wishlist_model', 'model');
    }

    /**
     * {
     *  itemId: number
     *  clientId: number
     * }
     */
    public function add_post() {
      $postData = json_decode($this->input->raw_input_stream, true);
      $insertId = $this->model->insert(array(
        "client_id" => $postData['client_id'],
        "item_id" => $postData['item_id']
      ));
      $response = array(
        "status" => "ok",
        "data" => $insertId
      );
      return $this->response($response, parent::HTTP_ACCEPTED);
    } 

    public function remove_delete() {
      $itemId = $this->input->get('item_id');
      $clientId = $this->input->get('client_id');
      $fav = $this->model->get_by(array(
        "item_id" => $itemId,
        "client_id" => $clientId
      ));
      if(!isset($fav)) {
        return $this->response(null, parent::HTTP_NOT_FOUND);
      }
      $result = $this->model->remove($fav['id']);
      return $this->response($result, parent::HTTP_ACCEPTED);
    }

    public function list_get() {
      $clientId = $this->input->get('client_id');
      $result = $this->model->get_many_by('client_id', $clientId);
      $response = array(
        "status" => 'ok',
        "data" => $result
      );
      return $this->response($response, parent::HTTP_OK);
    }

  }


?>