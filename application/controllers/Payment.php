<?php

use Restserver\Libraries\REST_Controller;

class Payment extends REST_Controller {

    protected function _getResponseInquiry($status, $message = 'Success', $order = array()) {
        $return = '';

        if ($status === '0') {
            $amount = floatval($order['grand_total']) - floatval($order['espay_fee_amount']);
            $espayPayment = explode(':', $order['espay_payment_method']);

            $productCode = $espayPayment[0];
            if ($productCode === 'CREDITCARD' || $productCode === 'BNIDBO') {

                $amount = floatval($order['grand_total']) - 0;
            }
            $return = '0;' . $message . ';' . $order['increment_id'] . ';' . number_format($amount, 2, '.', '') . ';' . $order['order_currency_code'] . ';Payment ' . $order['increment_id'] . ';' . date('d/m/Y h:i:s');
        } else {
            $return = '1;' . $message . ';;;;;';
        }

        return $return;
    }

    public function getData($no)
    {
        $query = $this->db->query("select * from mt_order where no_invoice = '$no'");
        if ($query->num_rows() > 0) {
            $data = [
                'orderid' => $query->result()[0]->no_invoice,
                'increment_id' => $query->result()[0]->order_id,
                'order_currency_code' => 'IDR',
                'status' => $query->result()[0]->status,
                'grand_total' => $query->result()[0]->total_w_tax,
                'espay_fee_amount' => 0,
                'date' => $query->result()[0]->date_created,
                'espay_payment_method' => 'BNIDBO:CREDITCARD',
                'ccfee' => 0,
            ];    
        }else{
            $data = [];
        }
        
        return $data;
    }

    public function inquiry_post() {
        $password = 'QHNADHYF';
        $defaultPaymentStatus = 'paid';
        $ccTrxFee = 0;

        $webServicePassword = $this->input->post('password');
        $signature =  $this->input->post('signature');
        $orderId =  $this->input->post('order_id');
        $rqDatetime =  $this->input->post('rq_datetime');
        $mode = 'INQUIRY';
        $selfSignature = $this->generatesignature($rqDatetime, $orderId, $mode);

        if ($signature === $selfSignature) {
            if ($webServicePassword == $password) {
                $orderData = $this->getData($orderId);
                if (!empty($orderData)) {
                    $orderData['ccfee'] = $ccTrxFee;
                    $orderData['espay_payment_method'] = 'transfer';

                    if ($orderData['status'] != $defaultPaymentStatus) {
                        $this->orderdetailinsert($orderData['orderid'],json_encode($_POST),$rqDatetime);
                        echo $this->_getResponseInquiry('0', 'Success', $orderData);
                    } else {
                        echo $this->_getResponseInquiry('1', 'Order Has been Processed');
                    }
                } else {
                    echo $this->_getResponseInquiry('1', 'Order Id Not Valid');
                }
            } else {
                echo $this->_getResponseInquiry('1', 'Failed');
            }
        } else {
            echo $this->_getResponseInquiry('1', 'Invalid Signature');
        }
    }
    
    public function generatesignature($rqDatetime, $orderId, $mode)
    {
        $key = 'j4zwh41qr5w2cod2';
        $str = '##'.$key.'##'.$rqDatetime.'##'.$orderId.'##'.$mode.'##';
        $uppercase = strtoupper($str);
        $signature = hash('sha256', $uppercase);
        return $signature;
    }

    public function orderdetailinsert($no_invoice,$raw_response,$date_created)
    {
        $q = $this->db->query("select no_invoice from mt_payment_order where no_invoice = '$no_invoice'");
        if ($q->num_rows() == 0) {
            $orderdetail = array(
                'payment_type' => 'espay',
                'payment_reference' => '1',
                'no_invoice' => $no_invoice,
                'raw_response' => $raw_response,
                'date_created' => $date_created
            );
            $this->db->insert('mt_payment_order', $orderdetail);
            $this->orderhistoryinsert($no_invoice);
        }
    }

    public function orderhistoryinsert($no_invoice)
    {
        $q = $this->db->query("select order_id,merchant_id from mt_order where no_invoice = '$no_invoice'");
        $orderdetail = array(
            'order_id' => $q->result()[0]->order_id,
            'status' => 'pending',
            'merchant_id' => $q->result()[0]->merchant_id
            );
        $this->db->insert('mt_order_history', $orderdetail);
        $this->db->set('payment_type', 'espay');
        $this->db->set('status', 'pending');
        $this->db->where('no_invoice', $no_invoice);
        $this->db->update('mt_order');
    }

    public function sendMail($data)
    {
        $this->load->library('email');

            $message = "
                        <html>
                        <head>
                            <title>Verification Code</title>
                        </head>
                        <body>
                            <h2>Thank you for Registering.</h2>
                        </body>
                        </html>
                        ";

            $this->email->initialize(array(
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.sendgrid.net',
            'smtp_user' => 'admiral08',
            'smtp_pass' => 'cijati211',
            'smtp_port' => 587,
            'crlf' => "\r\n",
            'newline' => "\r\n"
            ));
            
            $this->email->from('no_reply@okebid.com', 'okebid');
            $this->email->to($data['email']);
            $this->email->subject('Registrasi Client');
            $this->email->message($message);
            if(!$this->email->send()){
                    
            } else {
                return TRUE;
            }

    }
}