<?php
use Restserver\Libraries\REST_Controller;

class StreamingProduct extends REST_Controller {
	
	 function AllStreamingProduct_get(){
            $this->db->select('A.*');
            $this->db->from('streaming_product A');
            $myData = $this->db->get()->result();
            if($myData){
                $this->response(array('message'=>'success','data'=>$myData, 'code' => 200));
            }else{
                $this->response(array('message'=>'failed','data'=>null, 'code' => 202));
            }
        }
	
	
	function AllStreamingProductByStreaming_post(){
			
		    $id_streaming = $this->post('id_streaming');
		
		    $this->db->select('A.*');
            $this->db->from('streaming_product A');
		    $this->db->where('id_streaming', $id_streaming);
            $myData = $this->db->get()->result();
            if($myData){
                $this->response(array('message'=>'success','data'=>$myData, 'code' => 200));
            }else{
                $this->response(array('message'=>'failed','data'=>null, 'code' => 202));
            }
		
        }
	
	
	function AddStreamingProduct_post(){
      		
	
		
            $id_product_streaming = $this->post('id_product_streaming');
            $id_streaming = $this->post('id_streaming');
		    $name_product = $this->post('name_product');
			$subject_product = $this->post('subject_product');
			$qty_product = $this->post('qty_product');
			$stock_product = $this->post('stock_product');
			$min_harga_product = $this->post('min_harga_product');
		    $url_product = $this->post('url_product');
			$status_product = $this->post('status_product');
			$brand_id = $this->post('brand_id');
			$brand_name = $this->post('brand_name');
			$category = $this->post('category');
			$cat_main = $this->post('cat_main');
			$subcategory_id = $this->post('subcategory_id');
			$weight = $this->post('weight');
			$discount = $this->post('discount');
			$rating = $this->post('rating');
			$unit = $this->post('unit');
            
                        $data = array(
							'id_product_streaming' => NULL,
                            // 'id_merchant' => $id_merchant,
							'id_product_streaming'=> $id_product_streaming,
            				'id_streaming'=> $id_streaming,
							'name_product'=> $name_product,
							'subject_product'=> $subject_product,
							'qty_product' => $qty_product,
							'stock_product'=> $stock_product,
							'min_harga_product'=> $min_harga_product,
							'url_product'=> $url_product,
							'status_product'=> $status_product,
							'brand_id' => $brand_id,
							'category'=> $category,
							'cat_main'=> $cat_main,
							'subcategory_id' => $subcategory_id,
							'weight'=> $weight,
							'discount'=> $discount,
							'rating'=> $rating,
							'unit'=> $unit
                            );
                            
                    $myInsert = $this->db->insert('streaming_product', $data);
               
                    if($myInsert){
                        $this->response(array('message'=>'Tambah Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Tambah Data Streaming Gagal', 'code' => 202));
					}	
        }
	
	
	function DeleteStreamingProduct_post(){
      		
            $id_streaming = $this->post('id_streaming');
		$data = array(
                            'id_streaming' => $id_streaming,
			);
            $myInsert = $this->db->delete('streaming_product', $data);
               
                    if($myInsert){
                        $this->response(array('message'=>'Tambah Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Tambah Data Streaming Gagal', 'code' => 202));
					}	
        }
	
	
	function UpdateStreamingProduct_post(){
      		
		
		    $id_product_streaming = $this->post('id_product_streaming');
            $id_streaming = $this->post('id_streaming');
		    $name_product = $this->post('name_product');
			$subject_product = $this->post('subject_product');
			$qty_product = $this->post('qty_product');
			$stock_product = $this->post('stock_product');
			$min_harga_product = $this->post('min_harga_product');
		    $url_product = $this->post('url_product');
			$status_product = $this->post('status_product');
			$brand_id = $this->post('brand_id');$brand_name = $this->post('brand_name');
			$category = $this->post('category');
			$cat_main = $this->post('cat_main');
			$subcategory_id = $this->post('subcategory_id');
			$weight = $this->post('weight');
			$discount = $this->post('discount');
			$rating = $this->post('rating');
			$unit = $this->post('unit');
            
                        $data = array(
                            // 'id_merchant' => $id_merchant,
							'id_product_streaming'=> $id_product_streaming,
            				'id_streaming'=> $id_streaming,
							'name_product'=> $name_product,
							'subject_product'=> $subject_product,
							'qty_product' => $qty_product,
							'stock_product'=> $stock_product,
							'min_harga_product'=> $min_harga_product,
							'url_product'=> $url_product,
							'status_product'=> $status_product,
							'brand_id' => $brand_id,
							'category'=> $category,
							'cat_main'=> $cat_main,
							'subcategory_id' => $subcategory_id,
							'weight'=> $weight,
							'discount'=> $discount,
							'rating'=> $rating,
							'unit'=> $unit
                            );

                     $this->db->where('id_product_streaming', $id_product_streaming);
                     $myUpdate = $this->db->update('streaming_product', $data);
		
                    if($myUpdate){
                        $this->response(array('message'=>'Update Data Streaming Sudah Berhasil', 'code' => 200));
                    }else{
                        $this->response(array('message' => 'Update Data Streaming Gagal', 'code' => 202));
					}
        }
}