<?php

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class User extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model', 'client');
        $this->load->library('user_agent');
        
    }

    public function login_post(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        

        $mer = $this->db->query("select * from mt_client where email = '$email'");
        if($mer->num_rows() >0 ) {
            if (password_verify($password,$mer->result()[0]->password)) {
              $merchant = $mer->result()[0];
              $jwtPayload =array(
                  'client_id' => $merchant->client_id,
                  'timestamp' => time()
              );

              $token = md5(time());
              $hasiltoken=['token'=>$token];
              $response = array(
                  'status'=>'OK',
                  'result'=> $merchant,
                  'jwtToken' => $hasiltoken
              );
              $this->updatetoken($merchant->client_id,md5(time()));
              return $this->response($response, parent::HTTP_ACCEPTED);
            }
            else {
              return $this->response(array('status'=>'failed','error'=> 'Invalid password'), parent::HTTP_UNAUTHORIZED);  
            }
        }else {
            return $this->response(array('status'=>'failed','error'=> 'Invalid username'), parent::HTTP_UNAUTHORIZED);
        }
    }

    public function me_get() {
        //get from header
        needAuth();
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];
        $tokenData = validateToken($token);
        $user_id = $tokenData->client_id;
        $clientData = $this->client->get($user_id);
        return $this->response(
            array(
                'status' => 'ok',
                'result' => $clientData
            ),
            parent::HTTP_OK
        );
    }

    public function signup_post() {
        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $social_strategy = 'mobile';
        $status_client = 3;
        $date_created = date("Y-m-d H:i:s");
        $date_modified = date("Y-m-d H:i:s");
        
        $pass = password_hash($password, PASSWORD_BCRYPT);
        $key = md5(date('H:i:s'));
        $activation_key = substr($key,0,6);
        
        $stmt = $this->db->where('email',$email)->get('mt_client');
        if ($stmt->num_rows()>0) {
            return $this->response(array('status'=>'failed','error'=> 'Email or Phone already registered'), parent::HTTP_UNAUTHORIZED);
        }else{
          $sendemail=$this->sendMail($email,$password,$first_name,$last_name,$activation_key);
          if ($sendemail==TRUE) {
            $data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => $pass,
                'social_strategy' => $social_strategy,
                'activation_key' => $activation_key,
                'date_created' => $date_created,
                'date_modified' => $date_modified
            );

            $this->db->insert('mt_client',$data);
            $last_id = $this->db->insert_id();
            if ($last_id!=null) {
              $jwtPayload =array(
                'client_id' => $last_id,
                'timestamp' => time()
            );
              $token = generateToken($jwtPayload);
              $hasiltoken=['jwtToken'=>$token];
              $data_3 = array('email' => $email);
              $stmt_3 = $this->db->get_where('mt_client',$data_3);
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

    public function sendMail($email,$password,$first_name,$last_name,$activation_key)
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
                            <p>Name: ".$first_name."</p>
                            <p>Last Name: ".$last_name."</p>
                            <p>Email: ".$email."</p>
                            <p>Password: ".$password."</p>
                            <p>Code Activation: ".$activation_key."</p>
                            <p>Enter the activation code above</p>
                            <p>Please click the link below to activate your account.</p>
                            <h4><a href='https://api.okebid.com/index.php/Register/activateclient/".base64_encode($email)."/".$activation_key."'>Activate My Account</a></h4>
                        </body>
                        </html>
                        ";

            $this->email->initialize(array(
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.sendgrid.net',
            'smtp_user' => 'senookebid',
            'smtp_pass' => 'justd0it123',
            'smtp_port' => 587,
            'crlf' => "\r\n",
            'newline' => "\r\n"
            ));
            
            $this->email->from('no_reply@okebid.com', 'okebid');
            $this->email->to($email);
            $this->email->subject('Registrasi Client');
            $this->email->message($message);
            if(!$this->email->send()){
                    $error = $this->email->print_debugger();
                    $output = array ('stat'=> 'OK', 'err' => $error);
                    echo json_encode($output);
            } else {
                return TRUE;
            }

            }
            public function updatetoken($id,$data)
            {
                $this->db->set('android_token', $data);
                $this->db->where('client_id', $id);
                $this->db->update('mt_client');
            }

}?>