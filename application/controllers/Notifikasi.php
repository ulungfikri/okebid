<?php

use Restserver\Libraries\REST_Controller;

class Notifikasi extends REST_Controller {

    protected function _getResponseReport($status, $message = 'Success', $order = array()) {
        $return = '';

        if ($status === '0') {
            $return = '0,' . $message . ',' . $order['increment_id'] . ',' . $order['increment_id'] . ',' . date('Y-m-d H:i:s');
        } else {
            $return = '1,' . $message . ',,,';
        }

        return $return;
    }

    public function notif_post() {
        // $data = array(
        //     'data' => json_encode($_POST)
        // );
        //$this->db->insert('inquiry_save_post', $data);
        $password = 'QHNADHYF';
        $defaultPaymentStatus = 'waiting';


        $webServicePassword = $this->input->post('password');
        $orderId = $this->input->post('order_id');
        $paymentRef = $this->input->post('payment_ref');
        $product_code = $this->input->post('product_code');

        $signature = $this->input->post('signature');
        $rqDatetime = $this->input->post('rq_datetime');
        $mode = 'PAYMENTREPORT';

        $selfSignature = $this->generatesignature($rqDatetime, $orderId, $mode);

        $orderData = [];
        if ($signature == $selfSignature) {
            if ($webServicePassword == $password) {
                $orderData = $this->getData($orderId);
                if (!empty($orderData)) {
                    if ($orderData['status'] != $defaultPaymentStatus) {
                        try {
                            $status = '0';
                            $message = 'success';
                            $this->orderhistoryinsert($orderId,json_encode($_POST));
                        } catch (Exception $e) {
                            $status = '1';
                            $message = 'Update Order Failed';
                        }
                    } else {
                        $status = '1';
                        $message = 'Order has been processed';
                    }
                } else {
                    $status = '1';
                    $message = 'Order Id Not Valid';
                }
            } else {
                $status = '1';
                $message = 'Failed';
            }
        } else {
            $status = '1';
            $message = 'Invalid Signature';
        }
        echo $this->_getResponseReport($status, $message, $orderData);
    }

    public function generatesignature($rqDatetime, $orderId, $mode)
    {
        $key = 'j4zwh41qr5w2cod2';
        $str = '##'.$key.'##'.$rqDatetime.'##'.$orderId.'##'.$mode.'##';
        $uppercase = strtoupper($str);
        $signature = hash('sha256', $uppercase);
        return $signature;
    }

    public function getData($no)
    {
        $query = $this->db->query("select * from mt_order where no_invoice = '$no'");
        if ($query->num_rows() > 0) {
            $data = [
                'orderid' => $query->result()[0]->no_invoice,
                'increment_id' => $query->result()[0]->no_invoice,
                'status' => $query->result()[0]->status
            ];    
        }else{
            $data = [];
        }
        
        return $data;
    }
    public function orderhistoryinsert($no_invoice,$json)
    {
        $q = $this->db->query("select order_id,merchant_id from mt_order where no_invoice = '$no_invoice'");
        $orderdetail = array(
            'order_id' => $q->result()[0]->order_id,
            'status' => 'paid',
            'merchant_id' => $q->result()[0]->merchant_id
            );
        $this->db->insert('mt_order_history', $orderdetail);

        $this->db->set('json_notif',$json);
        $this->db->set('notif', '1');
        $this->db->set('status', 'paid');
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
        $this->db->where('no_invoice', $no_invoice);
        $this->db->update('mt_order');
    }

}