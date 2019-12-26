<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
// require APPPATH . '/libraries/Format.php';

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class Key extends REST_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load these helper to create JWT tokens
        // $this->load->helper(['jwt', 'authorization']);    
        $this->load->model('Api_model', 'Model');
        
    }

    public function hello_get()
    {
        $tokenData = '12344';
        
        // Create a token
        $token = generateToken($tokenData);

        // Set HTTP status code
        $status = parent::HTTP_OK;

        // Prepare the response
        $response = ['status' => $status, 'token' => $token];

        // REST_Controller provide this method to send responses
        $this->response($response, $status);

    }
    public function login_post()
    {
        // Have dummy user details to check user credentials
        // send via postman
        $dummy_user = [
            'username' => 'dev',
            'password' => 'dev'
        ];
        // Extract user data from POST request
        $username = $this->post('username');
        $password = $this->post('password');

        $cek = $this->Model->cek_login($username,$password);
        // Check if valid user
        if ($username === $dummy_user['username'] && $password === $dummy_user['password']) {
            
            // Create a token from the user data and send it as reponse
            $token = generateToken(['username' => $dummy_user['username']]);
            // Prepare the response
            $status = parent::HTTP_OK;
            $response = ['status' => $status, 'token' => $token];
            $this->response($response, $status);
        }
        else {
            $this->response(['msg' => 'Invalid username or password!'], parent::HTTP_NOT_FOUND);
        }
    }
    public function get_me_data_post()
    {
        // Call the verification method and store the return value in the variable
        // $data = $this->verify_request();
        $data = $this->verify_request1();

        // Send the return data as reponse
        
    }
    private function verify_request()
    {
        // Get all the headers
        $headers = $this->input->request_headers();

        // Extract the token
        $username = $headers['username'];
        $password = $headers['password'];


        
        // Check if valid user
        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $cek = $this->Model->cek_login($username,$password);
            if($cek->num_rows() > 0 ){
                $user = $cek->row();
                $token = generateToken($user->username);

                if($token === $user->token) {
                    return $user;
                }else{
                    $status = parent::HTTP_UNAUTHORIZED;
                    $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                    $this->response($response, $status);
    
                    exit();
                }
            }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }
    private function verify_request1()
    {
        // Get all the headers
        $headers = $this->input->request_headers();

        // Extract the token
        // $token = $headers['api_token'];
        $email = $headers['email'];
        $password = $headers['password'];
        $pass = md5($password);
        

        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $ntoken = generateToken($email.$pass);
            // $data = AUTHORIZATION::validateToken($token);
            $cek = $this->Model->cek_login($email, $password);
            if ($cek->num_rows() == 0) {
                $getData = $cek->row();
                // if ($ntoken = $getData->token) {
                    $status = parent::HTTP_OK;
                    $response = ['status' => $status, 'data' => $ntoken];
                    $this->response($response, $status);
                // }else {
                //     $status = parent::HTTP_UNAUTHORIZED;
                //     $response = ['status' => $status, 'msg' => 'Token Access Denied !'];
                //     $this->response($response, $status);
                // }
            }else {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'Username & Password Salah !!!'];
                $this->response($response, $status);
            }
            // if ($ntoken === false) {
            //     $status = parent::HTTP_UNAUTHORIZED;
            //     $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
            //     $this->response($response, $status);

            //     exit();
            // } else {
            //     $status = parent::HTTP_OK;
            //     $response = ['status' => $status, 'data' => $ntoken];
            //     $this->response($response, $status);
            //             // return TRUE;
            // }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }
 

}

/* End of file Api.php */