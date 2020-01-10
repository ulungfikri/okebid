<?php

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class Merchant extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Merchant_model', 'merchant');
        $this->load->library('user_agent');
        
    }

    public function login_post(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');


        //$merchantData = $this->merchant->getByEmailAndPassword($email, md5($password));
        $mer = $this->db->query("select * from mt_merchant where email = '$email'");
        if($mer->num_rows() >0 ) {
            if (password_verify($password,$mer->result()[0]->password)) {
              $merchant = $mer->result()[0];
              $jwtPayload =array(
                  'merchant_id' => $merchant->merchant_id,
                  'timestamp' => time()
              );

              $token = generateToken($jwtPayload);
              $hasiltoken=['jwtToken'=>$token];
              $response = array(
                  'status'=>'OK',
                  'result'=> $merchant,
                  'jwtToken' => $hasiltoken
              );
              return $this->response($response, parent::HTTP_ACCEPTED);
            }
            else {
              return $this->response(array('status'=>'failed','error'=> 'Invalid password'), parent::HTTP_UNAUTHORIZED);  
            }
        }else {
            return $this->response(array('status'=>'failed','error'=> 'Invalid username'), parent::HTTP_UNAUTHORIZED);
        }


    }

    public function signup_post()
    {  
    $contact_name = $this->input->post('contact_name');
    $email = $this->input->post('email');  
    $password = $this->input->post('password');
    
    $pass = password_hash($password, PASSWORD_BCRYPT);
    $contact_phone = $this->input->post('contact_phone');
    $key = md5(date('H:i:s'));
    $activation_key = substr($key,0,6);
    
    $stmt = $this->db->where('email',$email)->or_where('contact_phone',$contact_phone)->get('mt_merchant');
       if ($stmt->num_rows()>0) {
        return $this->response(array('status'=>'failed','error'=> 'Email or Phone already registered'), parent::HTTP_UNAUTHORIZED);
    }else{
      $sendemail=$this->sendMail($email,$password,$contact_phone,$activation_key);
      if ($sendemail==TRUE) {
        $data = array('contact_name'=>$contact_name, 'email'=>$email,'password'=>$pass, 'activation_key'=>$activation_key, 'contact_phone'=>$contact_phone);
        $this->db->insert('mt_merchant',$data);
        $last_id = $this->db->insert_id();
        if ($last_id!=null) {
          $jwtPayload =array(
            'merchant_id' => $last_id,
            'timestamp' => time()
        );
          $token = generateToken($jwtPayload);
          $hasiltoken=['jwtToken'=>$token];
          $data_3 = array('email' => $email);
          $stmt_3 = $this->db->get_where('mt_merchant',$data_3);
          $response = array(
            'status'=>'OK',
            'result'=> $stmt_3->row(),
            'jwtToken' => $hasiltoken
            );              
          return $this->response($response, parent::HTTP_ACCEPTED);
         
        }
      }    
    }
    return $this->response(array('status'=>'failed','error'=> 'Failed'), parent::HTTP_UNAUTHORIZED);
  }


public function sendMail($email,$password,$contact_phone,$activation_key)
{
  $this->load->library('email');

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
                        <p>Code Activation: ".$activation_key."</p>
                        <p>Enter the activation code above</p>
                        <p>Please click the link below to activate your account.</p>
                        <h4><a href='https://api.okebid.com/Register/activate/".$contact_phone."/".$activation_key."'>Activate My Account</a></h4>
                      </body>
                      </html>
                      ";

        $this->email->initialize(array(
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'seno.seno97s@gmail.com',  // Email gmail
            'smtp_pass'   => 'justd0it',  // Password gmail
            'smtp_crypto' => 'ssl',
            'smtp_port'   => 465,
            'crlf'    => "\r\n",
            'newline' => "\r\n"
        ));
        
        $this->email->from('no_reply@okebid.com', 'okebid');
        $this->email->to($email);
        $this->email->subject('Registrasi Merchant');
        $this->email->message($message);
          if(!$this->email->send()){
                $error = $this->email->print_debugger();
                 $output = array ('stat'=> 'OK', 'err' => $error);
                 echo json_encode($output);
          } else {
            return TRUE;
          }

        }

        
        public function active_post(){
          $merchant_id = $this->input->post('merchant_id');
          $merchantData = $this->merchant->active($merchant_id);
          if($merchantData===true ) {
              
              $response = array(
                  'status'=>'OK',
                  'result'=> 'Success'                  
              );
              return $this->response($response, parent::HTTP_ACCEPTED);
          }else {
              return $this->response(array('status'=>'failed','error'=> 'Failed to updating data'), parent::HTTP_UNAUTHORIZED);
          }
      }

      function selectKab_post()
    {
        $output = array();
        $cari = $this->input->post('kabupaten');
            $sql = "SELECT id_wilayah, province_name, kabupaten_name, kecamatan_name, lat, lng FROM sma_indonesia WHERE kabupaten_name LIKE '%$cari%' ";
            $get = $this->db->query($sql);
            foreach ($get->result() as $key => $value) {
              $result = array();
              $result['id_wilayah']   = $value->id_wilayah;
              $result['hasil'] = $value->province_name.', '. $value->kabupaten_name.', '.$value->kecamatan_name;
              $result['provinsi'] = $value->province_name;
              $result['kabupaten'] = $value->kabupaten_name;
              $result['kecamatan'] = $value->kecamatan_name;
              $result['lat'] = $value->lat;
              $result['lng'] = $value->lng;
              $output[]     = $result;
              }
            $status = parent::HTTP_OK;
            
            $response = ['status' => "OK", 'result' => $output];
            $this->response($response, $status);
    }

    public function bukaToko_post(){
      $merchant_id = $this->post('merchant_id');
      $uploadcuy= $this->upload();      
      if ($uploadcuy['upload'] === 'ok') {
        $fileData = $uploadcuy['fileData'];
        $update = array(      
          'merchant_name' => $this->input->post('merchant_name'),
          'description_merchant' => $this->input->post('description_merchant'),
          'prov' => $this->input->post('provinsi'),
          'kec' => $this->input->post('kecamatan'),
          'city' => $this->input->post('kabupaten'),
          'lat' => $this->input->post('lat'),
          'long' => $this->input->post('lng'),
          'street' => $this->input->post('street'),
          'image1' => $fileData['file_name']);            
        $insert = $this->merchant->update_merchant($merchant_id, $update);
          if($insert===TRUE){
              $output['status'] = 'OK';
              $output['result'] = $insert;
          }else{
              $output['status'] = array('status' => 'FAILED', 'code' => '200', 'message' => 'gagal nyimpen' );
          }
      // $this->response($output, 200);    
      }else {
        $output['status'] = array('status' => 'UPLOADING FAILED', 'code' => '200', 'message' => $uploadcuy['err'] );
      }
      $this->response($output, 200);    
             
       
    }
	
	public function update_post()
  {  
    $id = $this->input->post('merchant_id');
    $contact_name = $this->input->post('contact_name');
    $email = $this->input->post('email');  
    $password = $this->input->post('password');
    $pass = password_hash($password, PASSWORD_BCRYPT);;
    $contact_phone = $this->input->post('contact_phone');
    
    if ($id != '') {
      $data = array(
        'contact_name'=>$contact_name, 
        'contact_email'=>$email,
        'email'=>$email, 
        'contact_phone'=>$contact_phone
      );

      if ($password != '') {
        $data = array(
          'contact_name'=>$contact_name, 
          'password' => $pass,
          'contact_email'=>$email,
          'email'=>$email, 
          'contact_phone'=>$contact_phone
        );
      }
      $this->db->where('merchant_id',$id);
      $this->db->update('mt_merchant', $data);
      $q = $this->db->query("select * from mt_merchant where merchant_id = $id")->result();
      $response = array(
        'status'=>'OK',
        'result' => $q[0]
      );
  
      return $this->response($response, parent::HTTP_ACCEPTED);
    }else{
      return $this->response(array('status'=>'failed','error'=> 'Failed to updating data'), parent::HTTP_UNAUTHORIZED);
    }
  
  }
    
    public function upload()
    {
      $nama="";
      $result = array();
      $uploadPath = '/Applications/XAMPP/htdocs/okebid/upload/'; // ganti ke folder okebid
      $config['upload_path'] = $uploadPath;
      $config['allowed_types'] = 'png|jpg|jpeg|gif';
      $config['file_name'] = date('ymdHis');
      $config['max_size'] = 5000;
  
      $this->load->library('upload', $config);
      $this->upload->initialize($config);
      if($this->upload->do_upload('file')){
        $fileData = $this->upload->data();
        $hasil = array(
          'upload' => 'ok',
          'fileData' => $fileData,
         );        
      return $hasil;
      }else {
        $hasil['upload'] ='gagal';
        $hasil['err'] = $this->upload->display_errors();
        return $hasil;
        
      }
      
    }

    public function inserLogistik_post()
    {
        $logistik_id = $this->input->post('logistik_id');
        $merchant_id = $this->input->post('merchant_id');
        $data = array();
        foreach ($logistik_id as $value) {
            $dt = array();
            $dt['merchant_id'] = $merchant_id;
            $dt['logistik_id'] = $value;
            $data[] =$dt;           
        }
        $insert = $this->db->insert_batch('mt_logistik_merchant',$data);
        if ($insert==TRUE) {
          $status = parent::HTTP_OK;
          $response = ['status' => $status, 'result' => $data];
        }else{
          $status = 'NG';
          $response = ['status' => $status, 'messsage' => 'Failed to insert'];
        }
        
        $this->response($response, $status);
        
    }

    function getMerchantName_post()
    {
        $output = array();
        $merchant_id = $this->input->post('merchant_id');
            $sql = "SELECT * FROM mt_merchant WHERE merchant_id = '$merchant_id' ";
            $get = $this->db->query($sql);
            if($get->num_rows() >0 ) {
              $merchant = $get->result()[0];
              $response = array(
                  'status'=>'OK',
                  'result'=> $merchant
                  
              );
              return $this->response($response, parent::HTTP_ACCEPTED);
          }else {
              return $this->response(array('status'=>'failed','error'=> 'No data'), parent::HTTP_UNAUTHORIZED);
          }
    }




}?>