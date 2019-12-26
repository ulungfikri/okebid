<?php
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

 
class Cek_api extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Api_model', 'Api');
        $this->load->library('user_agent');
        
    }


    function get_bangsal_post()
    {
        
        $data = $this->verify_request1();
        $output = array();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_bangsal();
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_bangsal()->result();$this->Api->get_bangsal()->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        $this->response($output, 200);            
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
            $this->response($output, 200);
        }     
    }
    function get_kelas_post()
    {
        $output = array();
        $data = $this->verify_request1();
        if ($data === TRUE) {
            $cek_record = $this->Api->get_kelas();
            if($cek_record->num_rows() > 0 ){
                $output['status'] = array('status' => 'OK', 'code' => '200' );
                $output['record'] = $this->Api->get_kelas()->result();
            }else{
                $output['status'] = array('status' => 'OK', 'code' => '200', 'message' => 'no data avalibe' );
            }
        }else {
            $output['status'] =  parent::HTTP_UNAUTHORIZED;
        }
        $this->response($output, 200);
    }
    function cekip_get()
    {
        $ip_address = $this->input->ip_address();
        echo $ip_address;
    }
    private function verify_request()
    {
        $headers = $this->input->request_headers();

        $token = $headers['api_token'];
        try {
            $data = validateToken($token);
            if ($data === false) {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                $this->response($response, $status);

                // exit();
            } else {
                return TRUE;
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

        $username = $headers['username'];
        $password = $headers['password'];
        // $ip_address = $headers['ip_address'];
        $ip_address = $this->input->ip_address();

        try {
            $ntoken = generateToken($username.$password);
            $cek = $this->Api->cek_login($username, $password,$ip_address);
            if ($cek->num_rows() > 0) {
                $getData = $cek->row();
                if ($ntoken = $getData->token) {
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
                $response = ['status' => $status, 'msg' => 'Username & Password Salah !!!'];
                $this->response($response, $status);
            }
        } catch (Exception $e) {
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }
}