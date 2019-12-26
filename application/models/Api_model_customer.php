<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model_customer extends CI_Model {

    public function get_all_item($merchant_id){        
        $this->db->where('merchant_id', $merchant_id);
       // $this->db->from('');
        return $this->db->get('mt_item');
       
    }

    public function get_all_category(){
        $this->db->from('mt_category');
        return $this->db->get();
    }

    public function get_all_auctions(){
        $this->db->from('mt_auctions');
        return $this->db->get();
    }

    public function get_all_auctions_history(){
        $this->db->from('mt_auctions_history');
        return $this->db->get();
    }

    public function get_all_order($limit2,$offset2){
        $this->db->from('mt_order');
        $this->db->limit($limit2);
        $this->db->offset($offset2);
        return $this->db->get();
    }


    public function update_merchant($merchant_id,$update){
        $this->db->where('merchant_id',$merchant_id);
        return $this->db->update('mt_merchant',$update);
  
      }

      public function addProduct($insert){
        
        return $this->db->insert('mt_item',$insert);
  
      }

    function cek_login($email,$password)
    {
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        return $this->db->get('mt_merchant');
    }


}

/* End of file Api_model.php */