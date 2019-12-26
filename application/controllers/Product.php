<?php

use Restserver\Libraries\REST_Controller;

class Product extends REST_Controller {


    public function productwithauction_get()
    {
        try {
            $query = $this->db->query("select * from mt_item where type_sell = 'auction'")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function productlistauctionmerchant_post()
    {
        $id = $this->input->post('merchant_id');
        try {
            $query = $this->db->query("SELECT b.*,c.* FROM mt_merchant a JOIN mt_item b ON (a.merchant_id = b.merchant_id) JOIN mt_auctions c ON (c.item_id = b.item_id) WHERE a.merchant_id = $id")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function productlistall_post()
    {
        $id = $this->input->post('merchant_id');
        try {
            $query = $this->db->query("SELECT b.* FROM mt_merchant a JOIN mt_item b ON (a.merchant_id = b.merchant_id) WHERE a.merchant_id = $id ")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function productlistwhere_post()
    {
        $id = $this->input->post('merchant_id');
        $id2 = $this->input->post('typesell');
        try {
            $query = $this->db->query("SELECT b.* FROM mt_merchant a JOIN mt_item b ON (a.merchant_id = b.merchant_id) WHERE a.merchant_id = $id and b.type_sell = '$id2'")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    

    
    public function productget_get()
    {
        try {
            $query = $this->db->query("select * from mt_item order by rating_item  limit 10")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function productid_get()
    {
        $id = $this->input->get('productid');
        try {
            $query = $this->db->query("select * from mt_item where item_id = $id ")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function productoptstream_get()
    {
        try {
            $query = $this->db->query("SELECT * FROM mt_item WHERE stream = 'on' ORDER BY rating_item DESC LIMIT 1")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function producontstream_get()
    {
        try {
            $query = $this->db->query("SELECT * FROM mt_item WHERE stream = 'on' ORDER BY rating_item DESC LIMIT 10")->result();
            $response = array(
                "status" => 'ok',
                "product" => $query
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function onstream_get()
    {
        try {
            $id = $this->input->get('productid');
            $this->db->query("UPDATE mt_item SET stream = 'on' WHERE item_id = $id ");
            $response = array(
                "status" => 'ok',
                "message" => 'sukses '.$id.' on'
            );
            $this->response($response, parent::HTTP_OK);
        } catch (\Exceptopn $e) {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function offstream_post()
    {
        $id = $this->input->get('productid');
        $price = $this->input->get('finalprice');
        $client = $this->input->get('clientid');
        
        if ($id != '' && $price != '' && $client != '') {
            try {
    
                $query = $this->db->query("UPDATE mt_item SET stream = 'off' WHERE item_id = $id ");
                $this->db->query("UPDATE mt_auctions SET auction_client_winner = '$client', final_price = '$price' WHERE item_id = $id");
                $lelang = $this->db->query("SELECT * from mt_auctions where item_id = $id");
                $response = array(
                    "status" => 'ok',
                    "result" => $lelang->result()
                );
    
                $this->response($response, parent::HTTP_OK);
            } catch (\Exceptopn $e) {
                $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            $response = array(
                "status" => 'ok',
                "message" => 'failed'
            );

            $this->response($response, parent::HTTP_OK);
        }
    }

}
