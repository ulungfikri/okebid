<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Showinvoice extends CI_Controller {

    protected $inv= '';
    protected $token = '';
    public function __construct()
    {
        parent::__construct();
        $this->inv = $this->input->get('invoice');
        $this->token = $this->input->get('token');
    }
    
    public function index()
    {
        $data = array(
            'invoice' => $this->inv,
        );
        if ($this->token == '123456') {
            if ($this->inv != '') {
                $this->load->view('embedespayradio',$data);
            }else{
                echo "invoice tidak di temukan";
            }
        }else{
            echo "token tidak valid";
        }
    }


    public function InvoiceStatus()
    {
        // $no_invoice = $this->input->post('no_invoice');
        $order_id = $this->input->post('order_id');
        if ($order_id == null) {
            $arr = array(
                'status' => 'Error',
                'message'=> 'No invoice Tidak Ada'
            );
            return json_encode($arr);
            // die();
        }
        $q = $this->db->query("select order_id from mt_order where order_id = '$order_id'");
        if ($q->num_rows() == 0) {
            $arr = array(
                'status' => 'Error',
                'message'=> 'No invoice Tidak Ada'
            );
            return json_encode($arr);
            // die();
        }
        //echo var_dump($no_invoice);
        $rq_datetime = date("Y-m-d H:i:s");
        $order_id = $order_id;
        $rq_uuid = md5($order_id);
        $amount = '';
        $ccy = 'IDR';
        $comm_code = 'SGWOKEBID002';
        $mode = 'PAYMENTREPORT';

        echo $this->generatesignature_raw($rq_uuid ,$rq_datetime ,$order_id,$amount,$ccy ,$comm_code ,$mode);
        die();
        
        $curl = $this->curlPost('https://sandbox-api.espay.id/rest/merchantpg/sendinvoice',
         [
            'uuid' => $rq_uuid,
            'rq_datetime' => $rq_datetime,
            'comm_code' => $comm_code,
            'order_id' => $order_id,
            'signature' => $this->generatesignature($rq_uuid ,$rq_datetime ,$order_id,$amount,$ccy ,$comm_code ,$mode)
        ]);
        $jsondata = json_decode($curl);
        //var_dump($jsondata);
        // $this->UpdateDataCekStatus($jsondata->bank_name,$jsondata->expired,$no_invoice);
        echo json_encode($jsondata);
    }
    
    
    public function UpdateDataCekStatus($paytype,$duedate,$no_invoice)
    {
        try {
            $this->db->set('payment_type', $paytype);
            $this->db->set('due_date', $duedate);
            $this->db->where('no_invoice', $no_invoice);
            $this->db->update('mt_order');
        } catch (\Throwable $th) {
            die();
        }
    }
    
    public function curlPost($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error !== '') {
            throw new \Exception($error);
        }
    
        return $response;
    }

    public function generatesignature($rq_uuid ,$rq_datetime ,$order_id,$amount,$ccy ,$comm_code ,$mode)
    {
        $key = 'j4zwh41qr5w2cod2';
        $str = '##'.$key.'##'.$rq_datetime.'##'.$order_id.'##'.$mode.'##';
        $uppercase = strtoupper($str);
        $signature = hash('sha256', $uppercase);
        return $signature;
    }
    public function generatesignature_raw($rq_uuid ,$rq_datetime ,$order_id,$amount,$ccy ,$comm_code ,$mode)
    {
        $key = 'j4zwh41qr5w2cod2';
        $str = '##'.$key.'##'.$rq_datetime.'##'.$order_id.'##'.$mode.'##';
        $uppercase = strtoupper($str);
        $signature = hash('sha256', $uppercase);
        return $uppercase;
    }
    
    public function cekgenerate()
    {
        $rq_uuid = 'UUIDCEK100000001';
        $rq_datetime = '2019-10-13 11:12:00';
        $order_id = 'OKBID2019101319';
        $amount = '59000';
        $ccy = 'IDR';
        $comm_code = 'SGWOKEBID002';
        $mode = 'CHECKSTATUS';
        echo $this->generatesignature($rq_uuid ,$rq_datetime ,$order_id,$amount,$ccy ,$comm_code ,$mode);
    }
}
