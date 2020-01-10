<?php
use Restserver\Libraries\REST_Controller;

class Streaming extends REST_Controller {
	
	 function AllStreaming_get(){
            $this->db->select('A.*, B.*');
            $this->db->from('master_streaming A');
            $this->db->join('mt_merchant B', 'A.`id_merchant` = B.merchant_id', 'inner');
            $myData = $this->db->get()->result();
            if($myData){
                $this->response(array('message'=>'success','data'=>$myData, 'code' => 200));
            }else{
                $this->response(array('message'=>'failed','data'=>null, 'code' => 202));
            }
        }
	
	
	function AllStreamingMerchant_post(){
			
		    $id_merchant = $this->post('id_merchant');
		
            $this->db->select('A.*, B.*');
            $this->db->from('master_streaming A');
            $this->db->join('mt_merchant B', 'A.`id_merchant` = B.merchant_id', 'inner');
		    $this->db->where('id_merchant', $id_merchant);
            $myData = $this->db->get()->result();
            if($myData){
                $this->response(array('message'=>'success','data'=>$myData, 'code' => 200));
            }else{
                $this->response(array('message'=>'failed','data'=>null, 'code' => 202));
            }
        }
	
	
	function AddStreaming_post(){
      		
            $id_merchant = $this->post('id_merchant');
            $name_streaming = $this->post('name_streaming');
		    $subject = $this->post('subject');
			$detail = $this->post('detail');
			$date_start = $this->post('date_start');
			$date_end = $this->post('date_end');
			$status_streaming = $this->post('status_streaming');
            
                        $data = array(
                            'id_merchant' => $id_merchant,
							'name_streaming' => $name_streaming,
							'subject' => $subject,
							'detail' => $detail,
							'date_start' => $date_start,
							'date_end' => $date_end,
							'status_streaming' => $status_streaming
                            );
                            
                    $myInsert = $this->db->insert('master_streaming', $data);
               
                    if($myInsert){
                        $this->response(array('message'=>'Tambah Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Tambah Data Streaming Gagal', 'code' => 202));
					}	
        }
	
	
	function DeleteStreaming_post(){
      		
            $id_streaming = $this->post('id_streaming');
            $myInsert = $this->db->delete('master_streaming', $id_streaming);
               
                    if($myInsert){
                        $this->response(array('message'=>'Tambah Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Tambah Data Streaming Gagal', 'code' => 202));
					}	
        }
	
	
	function UpdateStreaming_post(){
      		
		
		    $id_streaming = $this->post('id_streaming');
		    $id_merchant = $this->post('id_merchant');
            $name_streaming = $this->post('name_streaming');
		    $subject = $this->post('subject');
			$detail = $this->post('detail');
			$date_start = $this->post('date_start');
			$date_end = $this->post('date_end');
			$status_streaming = $this->post('status_streaming');
		
              $data = array(
                            'id_merchant' => $id_merchant,
							'name_streaming' => $name_streaming,
							'subject' => $subject,
							'detail' => $detail,
							'date_start' => $date_start,
							'date_end' => $date_end,
							'status_streaming' => $status_streaming
                            );

                     $this->db->where('id_streaming', $id_streaming);
                     $myUpdate = $this->db->update('master_streaming', $data);
		
                    if($myUpdate){
                        $this->response(array('message'=>'Update Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Update Data Streaming Gagal', 'code' => 202));
					}
        }
}