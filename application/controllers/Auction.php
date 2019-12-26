<?php

use Restserver\Libraries\REST_Controller;

class Auction extends MY_Controller {
  const TYPE_TIME = 0;
  const TYPE_LIVE = 1;

  function __construct() 
  {
    parent::__construct();
    parent::loadModel('Auction_model');
    $this->load->model('AuctionHistory_model', 'history');
  }

  public function time_auction_get() {
    $size = $this->input->get('size');
    if(!isset($size)) {
      $size = 10;
    }
    $this->model->limit($size);
    $data = $this->model->get_many_by(array(
      "auction_type" => self::TYPE_TIME,
      "status" => "open",
    ));
    $response = array(
      "status" => "ok",
      "result" => $data
    );
    $this->response($response, parent::HTTP_OK);
  }

  public function auction_history_get() {
    $auctionId = $this->input->get('auctionId');
    if(!isset($auctionId)) {
      return $this->response(null, parent::HTTP_NOT_FOUND);
    }
    $data = $this->history->get_where('auction_id', $auctionId);
    $response = array(
      "status" => 'ok',
      "data" => $data
    );
  }

  /**
   * list of auction that joined
   */
  public function joined_get() {
    $limit = 10;
    $clientId = $this->input->get('client_id');


    
  }

  public function bid_post() {
    $postData = json_decode($this->input->raw_input_stream, true);
    if(!isset($postData['client_id']) || !isset($postData['auction_id']) || !isset($postData['price'])) {
      $this->response(array(
        "status" => "error",
        "error" => "Missing clientId or auctionId or price"
      ), parent::HTTP_BAD_REQUEST);
    }

    //cek wallet
    // wallet gimana?????
    // TODO: add check wallet
    
    $lastAuction = $this->lastAuctionBid($postData['auction_id']);
    $bidId = null;
    if(!isset($lastAuction)) {
      $bidId = $this->addBid($postData);
      $this->response(
        array(
          "status" => "ok",
          "bidId" => $bidId
        ),
        parent::HTTP_OK
      );
    }else {
      if($postData['price'] <= $lastAuction->price) {
        return $this->response(
          array(
            "status" => "error",
            "error" => "Bid value must be higher"
          ),
          parent::HTTP_BAD_REQUEST
        );
      } else {
        $bidId = $this->addBid($postData);
        $this->response(
          array(
            "status" => "ok",
            "bidId" => $bidId
          ),
          parent::HTTP_OK
        );
      }
    }
  }

  /**
   * Ambil bid terakhir dari auction
   */
  private function lastAuctionBid($auctionId) {
    $this->history->limit(1);
    $this->history->order_by("date_created", "desc");
    $data = $this->history->get_by('auction_id', $auctionId);
    return $data;
  }

  /**
   * Lakukan bid
   */
  private function addBid($postData) {
    $insertId = $this->history->insert(array(
      "client_id" => $postData['client_id'],
      "auction_id" => $postData['auction_id'],
      "price" => $postData['price'],
      "date_created" => time(),
    ));
    return $insertId;
  }
 //-----------------------------------------------------------------------------------------
 public function productauctiondetail_get()
 {
     try {
         $id = $this->input->get('productid');
         $query = $this->db->query("SELECT * FROM mt_auctions WHERE item_id = $id")->result();
         $response = array(
             "status" => 'ok',
             "product" => $query
         );
         $this->response($response, parent::HTTP_OK);
     } catch (\Exceptopn $e) {
         $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
     }
 }
 public function auctionclient_post()
    {
        try {
            $id = $this->input->post('productid');
            $query = $this->db->query("SELECT b.* FROM mt_auctions a JOIN mt_auctions_history b ON (a.id = b.client_id) WHERE a.item_id = $id")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
	public function auctionmessage_post()
	  {
		try {
		  $item_id = $this->input->post('item_id');
		  $user_id = $this->input->post('user_id');
		  $message = $this->input->post('message');
		  $data = array(
			'item_id' => $item_id,
			'user_id' => $user_id,
			'message' => $message
		  );
		  $this->db->insert('mt_auction_message', $data);
		  $response = array(
			"status" => 'ok'
		  );
		  $this->response($response, parent::HTTP_OK);
		} catch (\Exceptopn $e) {
		  $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
		}
	  }


}

