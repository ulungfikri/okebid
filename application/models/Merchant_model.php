<?php

class Merchant_model extends MY_Model {
    public $_table = 'mt_merchant';
    public $primary_key = 'merchant_id';


    public function getByEmailAndPassword($email, $password) {
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        return $this->db->get($this->_table);
    }

    public function active($merchant_id)
    {
        $this->db->where($this->primary_key,$merchant_id);
        $this->db->set('status_merchant', 'active');
        $this->db->update($this->_table);
        return true;
    }

    public function update_merchant($merchant_id,$update){
        $this->db->where($this->primary_key,$merchant_id);
        return $this->db->update($this->_table,$update);
    }
}
