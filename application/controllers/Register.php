<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
   
    $this->load->model('users_model');
    
  }

  public function activate(){
		$contact_phone =  $this->uri->segment(3);
		$activation_key = $this->uri->segment(4);

		//fetch user details
		$user = $this->users_model->getUser($contact_phone);

		//if code matches
		if($user['activation_key'] == $activation_key){
			//update user active status
			$data['status_merchant'] = '1';
      $query = $this->users_model->activate($data, $contact_phone);
      echo("Berhasil");
      
		
		}else{
      echo("gagal");
    }
		
		

	}


  public function activateClient(){
		$emailencd =  $this->uri->segment(3);
		$activation_key = $this->uri->segment(4);

		$email= base64_decode($emailencd);
		//fetch user details
		$user = $this->db->query("select * from mt_client where email = '$email'");
		if ($user->num_rows() > 0) {
			$this->db->query("update mt_client set status_client = 1 where email = '$email' ");
			echo "berhasil";
		}else{
			echo "email tidak di temukan";
		}
	}
}

/* End of file Register.php */
