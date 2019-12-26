<?php

class Client_model extends MY_Model {
    public $_table = 'mt_client';
    public $primary_key = 'client_id';


    public function getByEmailAndPassword($email, $password) {
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        return $this->db->get($this->_table);
    }
}
