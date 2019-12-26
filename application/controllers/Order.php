<?php
use Restserver\Libraries\REST_Controller;
class Order extends REST_Controller {

  public function orderhistory_post()
	{		
    $token = $this->input->post('token');
    $id    = $this->input->post('client_id');

    if ($this->cektokenclient($id,$token) > 0) {
        $order = $this->db->query("SELECT a.*,group_concat(d.merchant_name separator ',') title,
        GROUP_CONCAT(
          DISTINCT CONCAT(c.item_name,'#$',c.item_id) 
          SEPARATOR '^&' 
        ) merchant_data,
        group_concat(c.item_name separator ',') title2 
        FROM mt_order a
        
        JOIN mt_order_merchant b
        ON a.order_id = b.order_id
        JOIN  mt_merchant d
        ON b.merchant_id=d.merchant_id
        
        JOIN mt_order_details c 
        ON a.order_id = c.order_id
        WHERE  a.client_id = '$id'
        GROUP BY a.order_id
        ORDER BY a.order_id DESC");

      if ($order->num_rows() > 0) {
        foreach ($order->result_array() as $val) {
          $val['title'] = implode(',',array_unique(explode(',',$val['title'])));
          $val['title2'] = implode(',',array_unique(explode(',',$val['title2'])));
          $data[]=array(
            'order_id'=>$val['order_id'],
            'title'=>(strlen($val['title']) > 938)? substr($val['title'],0,935).'...': $val['title'],
            'title2'=>(strlen($val['title2']) > 938)? substr($val['title2'],0,935).'...': $val['title2'],
            'merchant_data'=>$val['merchant_data'],
            'status'=>$val['status']
          );
        }
        $response = array(
          'status'=>'OK',
          'result' => $data
        );
        return $this->response($response, parent::HTTP_ACCEPTED);
      }else{
        $response = array(
          'status'=>'OK',
          'result' => ''
        );
        return $this->response($response, parent::HTTP_ACCEPTED);
      }
    }else{
      return $this->response(array('status'=>'failed','error'=> 'token tidak sama'), parent::HTTP_UNAUTHORIZED);
    }
	}

  public function cektokenclient($id,$token)
  {
    $jml = $this->db->query("select * from mt_client where client_id = $id and android_token = '$token'");
    return $jml->num_rows();
  }

}