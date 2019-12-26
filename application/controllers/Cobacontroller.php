<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cobacontroller extends CI_Controller {

    function __construct() 
    {
    }
    public function index()
    {
        $a = $this->load->input->post('title');
        echo $a;
    }
	
	public function in_get()
    {
        $a = $this->load->input->post('title');
        echo $a;
    }
	
	public function in()
    {
        echo "asdasd";
    }
	
	

}