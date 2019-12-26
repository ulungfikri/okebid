<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	public function getAllUsers(){
		$query = $this->db->get('mt_merchant');
		return $query->result();
	}

	public function query($query)
	{
		return $this->db->query($query);
	}
	public function insert($user){
		$this->db->insert('tb_user', $user);
		return $this->db->insert_id();
	}

	public function getUser($contact_phone){
		$query = $this->db->get_where('mt_merchant',array('contact_phone'=>$contact_phone));
		return $query->row_array();
	}

	public function activate($data, $contact_phone){
		$this->db->where('mt_merchant.contact_phone', $contact_phone);
		return $this->db->update('mt_merchant', $data);
	}

}
