<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Coba extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model', 'Api');
        
    }

    public function test()  {
        $data = array(
            "a_variable" => "This is a variable",
            "user" =>"Johndoedoe",
            "code" => "qweasd"
        );
        $response = $this->twig->render("token", $data);
        echo $response;
    }
    

    public function index()
    {
        
        
        if($this->input->post('id')==''){
            $status = array('status' => 'OK', 'code' => '200' );
            $record = $this->Api->get_data()->result();
        }
        $this->setJSON($status,$record);
    }

    private function setJSON($status,$data){
        $response=array('status'=>$status,'result'=>$data);
        $this->output
        ->set_status_header(200)
        ->set_content_type('application/json','utf-8')
        ->set_output(json_encode($response,JSON_PRETTY_PRINT))
        ->_display();
        exit;
      }

}

/* End of file Coba.php */
