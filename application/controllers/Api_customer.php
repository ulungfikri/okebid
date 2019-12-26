<?php
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

 
class Api_customer extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model_customer', 'Api');
        $this->load->library('user_agent');
        
    }

    public function login_post(){
        $email = $this->input->post('email');
        $password = md5($this->input->post('password'));
        $data = array('email' => $email , 'password' =>$password, 'status_client'=>'active');
        $stmt = $this->db->get_where('mt_client',$data);
        if ($stmt->num_rows()>0) {
          $this->setJSON('OK',$stmt->result());
        }else {
          $this->setJSON('0',null);
        }
      }

      public function setJSON($sst,$data){
        $response=array('status'=>$sst,'result'=>$data);
        $this->output
        ->set_status_header(200)
        ->set_content_type('application/json','utf-8')
        ->set_output(json_encode($response,JSON_PRETTY_PRINT))
        ->_display();
        exit;
      }

      public function signup_post()
      {  
      $first_name = $this->input->post('first_name');
      $email = $this->input->post('email');  
      $password = $this->input->post('password');
      $pass = md5($password);
      $emailencd = urlencode($email);
      $stmt = $this->db->where('email',$email)->get('mt_client');
         if ($stmt->num_rows()>0) {
            $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'Email Sudah ada' );
  }else{
  
    $this->load->library('email');
                  $key = md5(date('H:i:s'));
                  $activation_key = substr($key,0,8);
                  $config = array(
                              'mailtype'  => 'html',
                              'charset'   => 'utf-8',
                              'protocol'  => 'smtp',
                              'smtp_host' => 'ssl://mail.5technosolution.com',
                              'smtp_user' => 'demo@5technosolution.com',
                              'smtp_pass' => 'Pandawa2019',
                              'smtp_port' => 465,
                              'crlf'      => "\r\n",
                              'newline'   => "\r\n"
                          );

                          $message = "
                    <html>
                    <head>
                      <title>Verification Code</title>
                    </head>
                    <body>
                      <h2>Thank you for Registering.</h2>
                      <p>Your Account:</p>
                      <p>Email: ".$email."</p>
                      <p>Password: ".$password."</p>
                      <p>Please click the link below to activate your account.</p>
                      <h4><a href='localhost/okebid/Register/activateClient/".$emailencd."/".$activation_key."'>Activate My Account</a></h4>
                    </body>
                    </html>
                    ";
  
                  $this->email->initialize($config);
                  $this->email->from($config['smtp_user'], 'OkeBid');
                  $this->email->to($email);
                  $this->email->subject('Complete Register');
                  $this->email->message('testing mail');
                  $this->email->message($message);

                 
                  if(!$this->email->send()){
                          $error = $this->email->print_debugger();
              $output = array ('stat'=> 'OK', 'err' => $error);
              echo json_encode($output);
                  } else {
                    $ntoken = AUTHORIZATION::generateToken($email.$pass);
                            
            $data = array('email'=>$email,'password'=>$pass, 'activation_key'=>$activation_key,'token'=>$ntoken,'first_name'=>$first_name);
  
            $stmt_2 = $this->db->insert('mt_client',$data);
  
            if ($stmt_2) {
              $data_3 = array('email' => $email);
  
              $stmt_3 = $this->db->get_where('mt_client',$data_3);              
              $this->setJSON('OK',$stmt_3->result());
    
  
            }
          }
   
  }
  echo json_encode(array("status" => FALSE));

      }




    public function get_all_item_post(){
        $merchant_id = $this->input->post('merchant_id');
        $data = $this->verify_request1();
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_all_item($merchant_id);
            if($cek_record->num_rows() > 0 ){
                $output['status'] = 'OK';
                $output['result'] = $this->Api->get_all_item($merchant_id)->result();
            }else{
               // $output['status'] = array('status' => 'NG', 'code' => '200', 'message' => 'no data avalibe' );
                $output['status'] = 'NG';
                $output['message'] = 'Sorry, No data avaible!';
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }

      public function get_all_category_post(){
        $data = $this->verify_request1();
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_all_category();
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_all_category()->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }

      public function get_all_auctions_post(){
        $data = $this->verify_request1();
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_all_auctions();
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_all_auctions()->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }

      public function get_all_auctions_history_post(){
        $data = $this->verify_request1();
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_all_auctions_history();
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_all_auctions_history()->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }

      public function get_all_order_post(){
        $data = $this->verify_request1();
        $limit2 = $this->post('brplimit');
        $offset2 = $this->post('brpoffset');
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_all_order($limit2);
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_all_order($limit2,$offset2)->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }


      public function bukaToko_post(){
        $data = $this->verify_request1();
        $merchant_id = $this->post('merchant_id');
        $update = array(      
            'merchant_name' => $this->input->post('merchant_name'),
            'street' => $this->input->post('street'));            
        if ($data === TRUE) {
            $insert = $this->Api->update_merchant($merchant_id, $update);
            if($insert===TRUE){
                $output['status'] = 'OK';
                $output['result'] = $insert;
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }

      function selectKab_post()
    {
        $output = array();
        $cari = $this->input->post('kabupaten');
            $sql = "SELECT id_wilayah, province_name, kabupaten_name, kecamatan_name FROM sma_indonesia WHERE kabupaten_name LIKE '%$cari%' ";
            $get = $this->db->query($sql);
		foreach ($get->result() as $key => $value) {
			$result = array();
			$result['id_wilayah']   = $value->id_wilayah;
			$result['text'] = $value->province_name.', '. $value->kabupaten_name.', '.$value->kecamatan_name;
			$output[]     = $result;
        }
        $status = parent::HTTP_OK;
        $response = ['status' => "OK", 'result' => $output];
        $this->response($response, $status);
    }

    public function addProduct_post(){
        $data = $this->verify_request1();
        
        $insert = array(      
            'merchant_id'=> $this->post('merchant_id'),
            'item_name' => $this->input->post('item_name'),
            'item_description' => $this->input->post('item_description'),
            'price' => $this->input->post('price'),
            'weight' => $this->input->post('weight'),
            'min_order' => $this->input->post('min_order'),
            'condition' => $this->input->post('condition'),
            'type_sell' => $this->input->post('type_sell'),);            
        if ($data === TRUE) {
            $insert = $this->Api->addProduct($insert);
            if($insert===TRUE){
                $output['status'] = 'OK';
                $output['result'] = $insert;
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
      }


    
    function cekip_get()
    {
        $ip_address = $this->input->ip_address();
        echo $ip_address;
    }

    private function verify_request1()
    {
        // Get all the headers
      //  $headers = $this->input->request_headers();
        $email = $this->input->post('email');
        $password = $this->input->post('password');

       

        try {
            $ntoken = AUTHORIZATION::generateToken($email.$password);
            $cek = $this->Api->cek_login($email,$password);
            if ($cek->num_rows() > 0) {
                $getData = $cek->row();
                if ($ntoken = $getData->activation_token) {
                    $status = parent::HTTP_OK;
                    $response = ['status' => $status, 'data' => $ntoken];
                    $this->response($response, $status);
                    return TRUE;
                }else {
                    $status = parent::HTTP_UNAUTHORIZED;
                    $response = ['status' => $status, 'msg' => 'Token Access Denied !'];
                    $this->response($response, $status);
                }
            }else {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'email & Password Salah !!!'];
                $this->response($response, $status);
            }
        } catch (Exception $e) {
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }
}