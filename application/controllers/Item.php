<?php 
class Item extends MY_Controller
{
    const NORMAL = 'normal';
    const AUCTION_TIME = 'time';

    public function __construct()
    {
        parent::__construct();
        parent::loadModel('Item_model');
        $this->load->model('Item_model', 'item');
        $this->load->model('Auction_model', 'auction');
        // if(!needAuth()) {
        //     redirect('page403');
        // }
    }

    public function get_by_id_post()
    {
        $merchant_id = $this->input->post('merchant_id');
        $type_sell = $this->input->post('type_sell');
        $itemData = $this->item->getById($merchant_id, $type_sell);
        if ($itemData->num_rows()>0) {
            $item = $itemData->result();
            $response = array(
                'status'=>'OK',
                'result'=> $item                
            );
            return $this->response($response, parent::HTTP_ACCEPTED);
        }else if ($itemData->num_rows() == null) {
           
            $response = array(
                'status'=>'NG',
                'message'=> 'Sorry no data'                
            );
            return $this->response($response, parent::HTTP_ACCEPTED);
        } else{
            return $this->response(array('error'=> 'No Data'), parent::HTTP_UNAUTHORIZED);
        }
    }

    public function search_get() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        if(!isset($page)) {
            $page = 1;
        }
        if(!isset($limit)) {
            $limit = 10;
        }

        $offset = ($page -1 ) * $limit;

        $category = $this->input->get('category'); // array of category
        $priceMin = $this->input->get('priceMin');
        $priceMax = $this->input->get('priceMax');
        $condition = $this->input->get('condition');
        $merchant = $this->input->get('merchant');
        $logistic = $this->input->get('logistic');
        $location = $this->input->get('location');
        $query = $this->input->get('query');
        $brand = $this->input->get('brand');

        if(isset($category)) {
            $this->db->where_in('category', $category);
        }
        if(isset($priceMin)) {
            $this->db->where('price >=', $priceMin);
        }

        if(isset($priceMax)) {
            $this->db->where('price <=', $priceMax);
        }

        if(isset($condition)) {
            $this->db->where('condition', $condition);
        }

        if(isset($brand)) {
            $this->db->where_in('brand_id', $brand);
        }

        if(isset($merchant)) {
            $this->db->where('merchant_id', $merchant);
        }

        if(isset($query)) {
            $this->db->like('item_name', $query);
            $this->db->or_like('item_description', $query);
        }
        $this->db->limit($limit, $offset);

        $query = $this->db->get('mt_item');
        $data = array();
        foreach($query->result() as $row) {
            array_push($data, $row);
        }

        $response = array(
            "count"=> count($data),
            "data"=> $data,
            "status" => "ok"
        );
        $this->response($response, 200);
    }

    public function latest_get() {
        $size = $this->input->get("size");
        if(!isset($size)) {
            $size = 10;
        }
        $this->model->order_by("date_created", "desc");
        $this->model->limit($size);
        $data = $this->model->get_all();
        $response = array(
            "count"=> count($data),
            "data"=> $data,
            "status" => "ok"
        );
        $this->response($response, 200);
    }

    public function baru_get() {
        $this->db->limit(10);
        $this->db->order_by('date_created', 'desc');
        $data = $this->db->get('mt_item');
        $this->response(
            array(
                'status' => "ok",
                "data" => $data->result()
            ),
            200
        );
    }

    public function most_get() {
        $size = $this->input->get("size");
        if(!isset($size)) {
            $size = 10;
        }
        $sql = "select i.* from mt_order_details od join mt_item i where od.item_id = i.item_id group by i.item_id  order by count(od.item_id) desc limit ?" ;
        $res = $this->db->query(
            $sql, array($size)
        );
        $data = $res->result();
        $response = array(
            "count"=> count($data),
            "data"=> $data,
            "status" => "ok"
        );
        $this->response($response, 200);

    }

    /**
     * upload image to the post
     */
    public function add_image_post() {
        $itemId = $this->input->post('item_id');
        $uploadResult= $this->upload();
        if ($uploadResult['upload'] === 'ok') {
            $fileData = $uploadResult['fileData'];
            $updatePayload = array(
                'photo' => $fileData['file_name']
            );
            $res = $this->model->update($itemId, $updatePayload);
            return $this->response(
                array(
                    "status" => "ok",
                    "result" => $res
                ),
                parent::HTTP_ACCEPTED
            );

        }else{
            return $this->response(
                array(  'status' => 'UPLOADING FAILED', 
                        'code' => '200', 
                        'message' => $uploadResult['err']), 
                parent::HTTP_BAD_GATEWAY );
        }
    }

    public function add_post() {
        $postData = json_decode($this->input->raw_input_stream, true);
        $mtid = $postData['merchant_id'];
        $response = array();
        $item = array(
            'merchant_id'=> $postData['merchant_id'],
            'item_name' => $postData['item_name'],
            'item_description' => $postData['item_description'],
            'price' => $postData['price'],
            'weight' => $postData['weight'],
            'min_order' => $postData['min_order'],
            'condition' => $postData['condition'],
            'type_sell' => $postData['type_sell']
        );
        $savedItem = $this->model->insert($item);
        $response['item'] = $savedItem;
        if($postData['type_sell'] == self::AUCTION_TIME) {
            $url = "merc=".$mtid."&item=".$savedItem['id'];
            $auction = array(
                'item_id' => $savedItem['id'],
                'auction_type' => $postData['type_sell'],
                'start_time' => $postData['start_date'],
                'end_time' => $postData['end_date'],
                'start_price' => $postData['start_price'],
                'max_price' => $postData['end_price'],
                'status' => 'open',
                'socket_url' => $url,
                'stream_url' => $url
            );
            $savedAuction = $this->auction->insert($auction);
            $response['auction'] = $savedAuction;
            $response['status'] = "ok";
        }
    return $this->response($response, parent::HTTP_ACCEPTED);
}

    public function upload_post()
    {
        $this->load->helper(array('form', 'url'));
        $filename = md5(time());
        $config['upload_path']          = '../httpdocs/storage/app/public/images/products';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 10000;
        $config['file_name']            = $filename;
        $this->load->library('upload', $config);
        
        if ( ! $this->upload->do_upload('berkas')){
            $error = array('error' => $this->upload->display_errors());
            var_dump($error);
        }else{
            $data = array('upload_data' => $this->upload->data());
            var_dump($data);
        }
    
    }

    public function tambahproduk_post()
    {
        $id = $this->input->post('merchant_id');
        $item_name = $this->input->post('item_name');
        $item_description = $this->input->post('item_description');
        $stock = $this->input->post('stock');
        $min_order = $this->input->post('min_order');
        $condition = $this->input->post('condition');
        $cat_main = $this->input->post('cat_main');
        $category = $this->input->post('category');
        $brand_id = $this->input->post('brand_id');
        $price = $this->input->post('price');
        $discount = $this->input->post('discount');
        $weight = $this->input->post('weight');
        $unit = $this->input->post('unit');
        $besar_kecil = $this->input->post('besar_kecil');
        $panjang_pendek = $this->input->post('panjang_pendek');
        $motor = $this->input->post('motor');
        $pickup = $this->input->post('pickup');
        $mobil = $this->input->post('mobil');
        $truk = $this->input->post('truk');
        $status = $this->input->post('status');

        if ($item_name != '' && $item_description != '') {
            try {
                $data = array(
                    'merchant_id' => $id,
                    'item_name' => $item_name,
                    'item_description' => $item_description,
                    'stock' => $stock,
                    'min_order' => $min_order,
                    'condition' => $condition,
                    'cat_main' => $cat_main,
                    'category' => $category,
                    'brand_id' => $brand_id,
                    'price' => $price,
                    'discount' => $discount,
                    'weight' => $weight,
                    'unit' => $unit,
                    'besar_kecil' => $besar_kecil,
                    'panjang_pendek' => $panjang_pendek,
                    'motor' => $motor,
                    'pickup' => $pickup,
                    'mobil' => $mobil,
                    'truk' => $truk,
                    'status' => $status
                );
                $this->db->insert('mt_item', $data);
                $response = array(
                    'status'=>'OK',
                    'result'=> 'reload'
                );              
                return $this->response($response, parent::HTTP_ACCEPTED);
            } catch (\Exception $e) {
                return $this->response(array('status'=>'failed','error'=> 'Failed'), parent::HTTP_UNAUTHORIZED);    
            }
        }else {
            return $this->response(array('status'=>'failed','error'=> 'Failed'), parent::HTTP_UNAUTHORIZED);
        }
    }


}
?>