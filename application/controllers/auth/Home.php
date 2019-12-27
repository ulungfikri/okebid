<?php
use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class home extends REST_Controller {
    
    
  
    function Slide_get() {
        
        $this->db->select('id, title, image, status, description, overlay, sort_id, opacity');
        $this->db->from('sliders');
        $Kategory = $this->db->get()->result();
            
         if($Kategory){
             $this->response(array('code'=>200,'data'=>$Kategory, 'message' => 'Document Tersedia'));
         }else{
             $this->response(array('code'=>202,'data'=>null, 'message' => 'Document Tersedia'));
         }
    }
    
    
    
    function Kategory_get() {
        
        $this->db->select('main_id, category_name, category_description, photo, status, tampil, sequence, color, date_created, date_modified, deleted_at, date_deleted ');
                      $this->db->from('mt_category_main');
          $Kategory = $this->db->get()->result();
         
         if($Kategory){
             $this->response(array('code'=>200,'data'=>$Kategory, 'message' => 'Document Tersedia'));
         }else{
             $this->response(array('code'=>202,'data'=>null, 'message' => 'Document Tersedia'));
         }
         
    }
    
    
    
     function NewLelang_get() {
         
        $this->db->select('
        
        A.`id`, A.`item_id`, B.`item_name`, B.`photo` , B.`item_description`,B.`price`, B.`discount_percent` , A.`auction_type`, A.`start_time`, A.`end_time`, A.`start_price`, A.`max_price`, A.`final_price`, A.`auction_client_winner`, A.`status`, A.`stream_url`, A.`socket_url`, A.`session_id`, A.`buy_it_now_price`, A.`increment`, A.`reserve_price`, A.`date_created`, A.`date_updated`, A.`deleted_at`, A.`date_deleted` 
        
        ');
          
          $this->db->from('`mt_auctions` AS A');
          $this->db->join('mt_item AS B ', 'A.item_id = B.item_id','inner');
          $Kategory = $this->db->get()->result();
         
         if($Kategory){
             $this->response(array('code'=>200,'data'=>$Kategory, 'message' => 'Document Tersedia'));
         }else{
             $this->response(array('code'=>202,'data'=>null, 'message' => 'Document Tersedia'));
         }
         
    }
    
    
    function Terlaris_get() {
        
        $this->db->select('
        
        `item_id`, `product_id`, `merchant_id`, `item_name`, `item_description`, `brand_id`, `brand_name`, `wholesale_flag`, `status`, `category`, `cat_main`, `subsubcategory_id`, `price`, `weight`, `discount`, `photo`, `image`, `rating_item`, `jml_review_item`, `sequence`, `is_featured`, `date_created`, `date_modified`, `item_name_trans`, `item_description_trans`, `non_taxable`, `not_available`, `gallery_photo`, `stock`, `hold_stock`, `wholesale_price`, `retail_price`, `location`, `views`, `sold`, `condition`, `uom`, `min_order`, `min_quantity`, `unit`, `tax`, `besar_kecil`, `panjang_pendek`, `motor`, `pickup`, `mobil`, `truk`, `discount_percent`, `type_sell`, `deleted_at`, `stream`
        
        ');
                     $this->db->from('mt_item');
                     $this->db->order_by('sold', 'asc');
          $mt_item = $this->db->get()->result();
         
         if($mt_item){
             $this->response(array('code'=>200,'data'=>$mt_item, 'message' => 'Document Tersedia'));
         }else{
             $this->response(array('code'=>202,'data'=>null, 'message' => 'Document Tersedia'));
         }
         
    }
    
    
    
    
    
    function ProductDetail_post (){
        
            $data = array('A.item_id' =>$this->post('item_id'));
            
            $this->db->select('
            
            A.`item_id`, A.`product_id`, B.* , C.category_name, C.category_description , A.`item_name`, A.`item_description`, A.`brand_id`, A.`brand_name`, A.`wholesale_flag`, A.`status`, A.`category`, A.`cat_main`, A.`subsubcategory_id`, A.`price`, A.`weight`, A.`discount`, A.`photo`, A.`image`, A.`rating_item`, A.`jml_review_item`, A.`sequence`, A.`is_featured`, A.`date_created`, A.`date_modified`, A.`item_name_trans`, A.`item_description_trans`, A.`non_taxable`, A.`not_available`, A.`gallery_photo`, A.`stock`, A.`hold_stock`, A.`wholesale_price`, A.`retail_price`, A.`location`, A.`views`, A.`sold`, A.`condition`, A.`uom`, A.`min_order`, A.`min_quantity`, A.`unit`, A.`tax`, A.`besar_kecil`, A.`panjang_pendek`, A.`motor`, A.`pickup`, A.`mobil`, A.`truk`, A.`discount_percent`, A.`type_sell`, A.`deleted_at`, A.`stream` 
            
            ');
            $this->db->from('mt_item A');
            $this->db->join('mt_merchant B', 'A.`merchant_id` = B.merchant_id', 'right');
            $this->db->join('mt_category C', 'A.category = C.cat_id', 'left');
            $this->db->where($data);
            $myData = $this->db->get()->result();
            
            if($myData){
                $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
            }else{
                $this->response(array('status'=>'failed','message'=>null, 'code' => 202));
            }
    }



    function SubCategory_post (){
        
            $data = array('cat_main' =>$this->post('cat_main'));
            
            $this->db->select('
             `item_id`, `product_id`, `merchant_id`, `item_name`, `item_description`, `brand_id`, `brand_name`, `wholesale_flag`, `status`, `category`, `cat_main`, `subsubcategory_id`, `price`, `weight`, `discount`, `photo`, `image`, `rating_item`, `jml_review_item`, `sequence`, `is_featured`, `date_created`, `date_modified`, `item_name_trans`, `item_description_trans`, `non_taxable`, `not_available`, `gallery_photo`, `stock`, `hold_stock`, `wholesale_price`, `retail_price`, `location`, `views`, `sold`, `condition`, `uom`, `min_order`, `min_quantity`, `unit`, `tax`, `besar_kecil`, `panjang_pendek`, `motor`, `pickup`, `mobil`, `truk`, `discount_percent`, `type_sell`, `deleted_at`, `stream`
            ');
            $this->db->from('mt_item');
            $this->db->where($data);
            $myData = $this->db->get()->result();
            
            if($myData){
                $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
            }else{
                $this->response(array('status'=>'failed','message'=>null, 'code' => 202));
            }
    }


     function SubCategoryMenu_post (){
        
            $data = array('main_id' =>$this->post('main_id'));
            
            $this->db->select('
             `cat_id`, `main_id`, `parent_id`, `category_name`, `category_description`, `photo`, `status`, `sequence`, `date_created`, `date_modified`, `category_name_trans`, `category_description_trans`, `deleted_at`
            ');
            $this->db->from('mt_category');
            $this->db->where($data);
            $myData = $this->db->get()->result();
            
            if($myData){
                $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
            }else{
                $this->response(array('status'=>'failed','message'=>null, 'code' => 202));
            }
    }


     function SubSubCategory_post (){
        
            $data = array('parent_id' =>$this->post('parent_id'));
            
            $this->db->select('
             `id`, `parent_id`, `name`, `description`, `photo`, `status`, `sort`, `created_at`, `updated_at`, `deleted_at`
            ');
            $this->db->from('mt_subsubcategory');
            $this->db->where($data);
            $myData = $this->db->get()->result();
            
            if($myData){
                $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
            }else{
                $this->response(array('status'=>'failed','message'=>null, 'code' => 202));
            }
    }
    
    

    
}

?>