<?php
use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class customer extends REST_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model', 'client');
        $this->load->library('user_agent');
        
    }

    public function login_post(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        

        $mer = $this->db->query("select * from mt_client where email = '$email'");
        if($mer->num_rows() >0 ) {
            if (password_verify($password,$mer->result()[0]->password)) {
              $merchant = $mer->result()[0];
              $jwtPayload =array(
                  'client_id' => $merchant->client_id,
                  'timestamp' => time()
              );

              $token = md5(time());
              $hasiltoken=['token'=>$token];
              $response = array(
                  'status'=>'OK',
                  'result'=> $merchant,
                  'jwtToken' => $hasiltoken
              );
              $this->updatetoken($merchant->client_id,md5(time()));
              return $this->response($response, parent::HTTP_ACCEPTED);
            }
            else {
              return $this->response(array('status'=>'failed','error'=> 'Invalid password'), parent::HTTP_UNAUTHORIZED);  
            }
        }else {
            return $this->response(array('status'=>'failed','error'=> 'Invalid username'), parent::HTTP_UNAUTHORIZED);
        }
    }

    public function me_get() {
        //get from header
        needAuth();
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];
        $tokenData = validateToken($token);
        $user_id = $tokenData->client_id;
        $clientData = $this->client->get($user_id);
        return $this->response(
            array(
                'status' => 'ok',
                'result' => $clientData
            ),
            parent::HTTP_OK
        );
    }

    public function signup_post() {
        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $social_strategy = 'mobile';
        $status_client = 3;
        $date_created = date("Y-m-d H:i:s");
        $date_modified = date("Y-m-d H:i:s");
        
        $pass = password_hash($password, PASSWORD_BCRYPT);
        $key = md5(date('H:i:s'));
        $activation_key = substr($key,0,6);
        
        $stmt = $this->db->where('email',$email)->get('mt_client');
        if ($stmt->num_rows()>0) {
            return $this->response(array('status'=>'failed','error'=> 'Email or Phone already registered'), parent::HTTP_UNAUTHORIZED);
        }else{
          $sendemail=$this->sendMail($email,$password,$first_name,$last_name,$activation_key);
          if ($sendemail==TRUE) {
            $data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => $pass,
                'social_strategy' => $social_strategy,
                'activation_key' => $activation_key,
                'date_created' => $date_created,
                'date_modified' => $date_modified
            );

            $this->db->insert('mt_client',$data);
            $last_id = $this->db->insert_id();
            if ($last_id!=null) {
              $jwtPayload =array(
                'client_id' => $last_id,
                'timestamp' => time()
            );
              $token = generateToken($jwtPayload);
              $hasiltoken=['jwtToken'=>$token];
              $data_3 = array('email' => $email);
              $stmt_3 = $this->db->get_where('mt_client',$data_3);
              $response = array(
                'status'=>'OK',
                'result'=> $stmt_3->row(),
                'jwtToken' => $hasiltoken
                );              
              return $this->response($response, parent::HTTP_ACCEPTED);
             
            }
          }    
        }
        // return $this->response(array('status'=>'failed','error'=> 'Failed'), parent::HTTP_UNAUTHORIZED);
        return $this->response(array('status'=>'success','error'=> 'success'), parent::HTTP_UNAUTHORIZED);
    }

    public function sendMail($email,$password,$first_name,$last_name,$activation_key)
    {
    $this->load->library('email');

            $message = "
                        <html>
                        <head>
                            <title>Verification Code</title>
                        </head>
                        <body>
                            <h2>Thank you for Registering.</h2>
                            <p>Your Account:</p>
                            <p>Name: ".$first_name."</p>
                            <p>Last Name: ".$last_name."</p>
                            <p>Email: ".$email."</p>
                            <p>Password: ".$password."</p>
                            <p>Code Activation: ".$activation_key."</p>
                            <p>Enter the activation code above</p>
                            <p>Please click the link below to activate your account.</p>
                            <h4><a href='https://api.okebid.com/index.php/Register/activateclient/".base64_encode($email)."/".$activation_key."'>Activate My Account</a></h4>
                        </body>
                        </html>
                        ";

            $this->email->initialize(array(
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'seno.seno97s@gmail.com',  // Email gmail
            'smtp_pass'   => 'justd0it',  // Password gmail
            'smtp_crypto' => 'ssl',
            'smtp_port'   => 465,
            'crlf'    => "\r\n",
            'newline' => "\r\n"
            ));
            
            $this->email->from('no_reply@okebid.com', 'okebid');
            $this->email->to($email);
            $this->email->subject('Registrasi Client');
            $this->email->message($message);
            if(!$this->email->send()){
                    $error = $this->email->print_debugger();
                    $output = array ('stat'=> 'OK', 'err' => $error);
                    echo json_encode($output);
            } else {
                return TRUE;
            }

            }
            public function updatetoken($id,$data)
            {
                $this->db->set('android_token', $data);
                $this->db->where('client_id', $id);
                $this->db->update('mt_client');
            }  

   function callCustomer($email, $password, $password_hash){
    if (password_verify($password,$password_hash)) {
        
        $data = array('email' => $email, 'password' => $password_hash);
        
        $this->db->select('*');
        $this->db->from('mt_client');
        $this->db->where($data);
        
        return  $customer = $this->db->get()->result();
    } else {
        return false;
        }
    }

    function checkEmailAlready($email){
        error_reporting(0);
        $data = array('email' =>$email);
        $this->db->select('`email`');
        $this->db->from('mt_client');
        $this->db->where($data);
        $row = $this->db->get()->row();
        
        if($row->email == $email){
            return true;
        }else{
            return false;
        }
    }


    function Message_post(){
        
        $id = $this->getMaxIdMessage();
        $data = array(
            'message_id' => $id,
            'client_id' => $this->post('client_id'),
            'merchant_id' => $this->post('merchant_id'),
            'created_at' => $this->post('created_at'));
        
        $myInsert = $this->db->insert('message', $data);
        
        if($myInsert){
            if($this->MessageDetail($id, $this->post('client_id'),$this->post('merchant_id'), $this->post('content'), $this->post('created_at'))==true){
                
               $this->response(array('status'=>'success','message'=>'sukses kirim pesan', 'code' => 200));
               
           }else{
               $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
           }
       }else{
         $this->response(array('status'=>'failed','message'=>null, 'code' => 502)); 
       }
    }


    function getMaxIdMessage(){
        $this->db->select_max('message_id');
        $this->db->from('message');
        $row = $this->db->get()->row();
        return $row->message_id+1;
    }



    function MessageDetail($message_id, $client_id, $merchant_id, $content, $created_at){

    $data = array(
    'message_id' => $message_id,
    'client_id' => $client_id,
    'merchant_id' => $merchant_id,
    'content' => $content,
    'created_at' => $created_at,
    );

    $myInsert = $this->db->insert('message_detail', $data);

        if($myInsert){
            return true;
        }else{
            return false;
        }
    }


    function GetMessage_post (){

        $data = array('A.client_id' =>$this->post('client_id'));

        $this->db->select('
            A.`message_id`, A.`client_id`, A.`merchant_id`, A.`created_at`, A.`updated_at`, A.`deleted_at`,
            B.id as messagedetail_id, B.content
            ');

        $this->db->from('message A');
        $this->db->join('message_detail B', 'A.`message_id` = B.message_id', 'right');
        $this->db->where($data);
        $myData = $this->db->get()->result();

        if($myData){
            $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
        }
    }



    function UpdateProfil_post(){


        $client_id = $this->post('idClient');
        $first_name = $this->post('firstName');
        $last_name = $this->post('lastName');
        $email = $this->post('email');
        $street = $this->post('alamat');
        $city = $this->post('kota');
        $zipcode = $this->post('kodePos');


       


           $data = array(
               'first_name' => $first_name,
               'last_name' => $last_name,
               'email' => $email,
               'street' => $street,
               'city' => $city,
               'zipcode' => $zipcode
           );

           $this->db->where('client_id', $client_id);
           $myInsert = $this->db->update('mt_client', $data);
           if($myInsert){

            $datas = array('client_id' => $client_id);

            $this->db->select('*');
            $this->db->from('mt_client');
            $this->db->where($datas);

            $customer = $this->db->get()->result();



            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));

        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }


  
    }



    function UpdateBank_post(){

        $idClient = $this->post('idClient');
        $fullname = $this->post('fullname');
        $bankName = $this->post('bankName');
        $typeAkunBank = $this->post('typeAkunBank');
        $noRekening = $this->post('noRekening');
        $KodeCvv = $this->post('KodeCvv');

        $data = array(
           'bank' => $bankName,
           'norek' => $noRekening,
           'namarek' => $fullname
       );

        $this->db->where('client_id', $idClient);
        $myInsert = $this->db->update('mt_client', $data);

        if($myInsert){

            $datas = array('client_id' => $idClient);

            $this->db->select('*');
            $this->db->from('mt_client');
            $this->db->where($datas);

            $customer = $this->db->get()->result();



            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));

        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }


    }



    function Alamat_post(){

        $client_id = $this->post('client_id');
        $datas = array('client_id' => $client_id);
        $this->db->select('*');
        $this->db->from('mt_address_book');
        $this->db->where($datas);
        $customer = $this->db->get()->result();

        if($customer){
            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }



    function UpdateAlamat_post(){

        $idAlamat = $this->post('idAlamat');
        $idClient = $this->post('idClient');
        $street = $this->post('street');
        $city = $this->post('city');
        $state = $this->post('state');
        $zipcode = $this->post('zipcode');
        $location_name = $this->post('location_name');
        $lat = $this->post('lat'); 
        $long = $this->post('longs');


        $data = array(
           'street' => $street,
           'city' => $city,
           'state' => $state,
           'zipcode' => $zipcode,
           'location_name' => $location_name,
           'lat' => $lat,
           'long' => $long
       );

        $dataWhere = array(
           'id' => $idAlamat,
           'client_id' => $idClient
       );

        $this->db->where($dataWhere);

        $myInsert = $this->db->update('mt_address_book', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }



    function TambahAlamat_post(){

        $idClient = $this->post('idClient');
        $street = $this->post('street');
        $city = $this->post('city');
        $state = $this->post('state');
        $zipcode = $this->post('zipcode');
        $location_name = $this->post('location_name');
        $lat = $this->post('lat'); 
        $long = $this->post('longs');


        $data = array(
           'client_id' => $idClient,
           'street' => $street,
           'city' => $city,
           'state' => $state,
           'zipcode' => $zipcode,
           'location_name' => $location_name,
           'lat' => $lat,
           'long' => $long
       );



        $myInsert = $this->db->insert('mt_address_book', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }


    function RemoveAlamat_post(){

        $idClient = $this->post('idClient');
        $idAlamat = $this->post('idAlamat');



        $data = array(
           'id' => $idAlamat,
           'client_id' => $idClient
       );


        $this->db->where($data);
        $myDelete =  $this->db->delete('mt_address_book');

        if($myDelete){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }



    function FavoriteProduct_post (){

        $data = array('A.client_id' =>$this->post('client_id'));

        $this->db->select('
            A.id as idFavorite, A.client_id, A.created_at, A.updated_at,
            B.* 
            ');

        $this->db->from('mt_fav_product A');
        $this->db->join('mt_item B', 'A.`item_id` = B.item_id', 'right');
        $this->db->where($data);
        $myData = $this->db->get()->result();

        if($myData){
            $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
        }
    }



    function AddFavorite_post(){

        $client_id = $this->post('client_id');
        $item_id = $this->post('item_id');


        $data = array(
         'client_id' => $client_id,
         'item_id' => $item_id
     );

        $myInsert = $this->db->insert('mt_fav_product', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }






    function UlasanProduct_post(){

        
        $data = array('A.item_id' =>$this->post('item_id'));

        $this->db->select('
            A.`id` AS idReviewProduct, A.`merchant_id`, A.`item_id`, A.`client_id`, A.`review`, A.`rating`, A.`status`, A.`date_created`, A.`date_modified`, A.`ip_address`, A.`order_id`, A.`deleted_at`, A.`updated_at`
            ,B.*, C.first_name, C.last_name 
            ');

        $this->db->from('mt_review A');
        $this->db->join('mt_item B', 'A.`item_id` = B.item_id', 'left');
        $this->db->join('mt_client C', 'A.`client_id` = C.client_id', 'inner');
        $this->db->where($data);
        $customer = $this->db->get()->result();
        
  
        if($customer){
            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }

    function AddProduct_post(){

        $item_id = $this->post('item_id');
        $client_id = $this->post('client_id');
        $review = $this->post('review');
        $rating = $this->post('rating');
        $status = $this->post('status');


        $data = array(
         'item_id' => $item_id,
         'client_id' => $client_id,
         'review' => $review,
         'rating' => $rating,
         'status' => $status
     );

        $myInsert = $this->db->insert('mt_review', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'failed','message' => null, 'code' => 502));
        }
    }


    function UpdateProduct_post(){

        $item_id = $this->post('item_id');
        $client_id = $this->post('client_id');
        $review = $this->post('review');
        $rating = $this->post('rating');
        $status = $this->post('status');

        $data = array(
           'item_id' => $item_id,
           'client_id' => $client_id,
           'review' => $review,
           'rating' => $rating,
           'status' => $status
       );

        $dataWhere = array(
           'item_id' => $item_id,
           'client_id' => $idClient
       );

        $this->db->where($dataWhere);

        $myInsert = $this->db->update('mt_review', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }


    function DeleteProduct_post(){

        $client_id = $this->post('client_id');
        $item_id = $this->post('item_id');



        $data = array(
         'client_id' => $client_id,
         'item_id' => $item_id
       );


        $this->db->where($data);
        $myDelete =  $this->db->delete('mt_review');

        if($myDelete){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }



    function UlasanToko_post(){

        $merchant_id = $this->post('merchant_id');
        $datas = array('A.merchant_id' => $merchant_id);
        $this->db->select(' A.`id` AS idReviewProduct, A.`merchant_id`, A.`item_id`, A.`client_id`, A.`review`, A.`rating`, A.`status`, A.`date_created`, A.`date_modified`, A.`ip_address`, A.`order_id`, A.`deleted_at`, A.`updated_at`
            ,B.*, C.first_name, C.last_name');
        $this->db->from('mt_review A');
        $this->db->join('mt_item B', 'A.`item_id` = B.item_id', 'left');
        $this->db->join('mt_client C', 'A.`client_id` = C.client_id', 'left');
        $this->db->where($datas);
        $customer = $this->db->get()->result();

        if($customer){
            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }

    function AddUlasanToko_post(){

        $merchant_id = $this->post('merchant_id');
        $client_id = $this->post('client_id');
        $review = $this->post('review');
        $rating = $this->post('rating');
        $status = $this->post('status');


        $data = array(
         'merchant_id' => $merchant_id,
         'client_id' => $client_id,
         'review' => $review,
         'rating' => $rating,
         'status' => $status
     );

        $myInsert = $this->db->insert('mt_review', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'failed','message' => null, 'code' => 502));
        }
    }

    function UpdateToko_post(){

        $item_id = $this->post('item_id');
        $client_id = $this->post('client_id');
        $review = $this->post('review');
        $rating = $this->post('rating');
        $status = $this->post('status');

        $data = array(
           'item_id' => $item_id,
           'client_id' => $client_id,
           'review' => $review,
           'rating' => $rating,
           'status' => $status
       );

        $dataWhere = array(
           'item_id' => $item_id,
           'client_id' => $idClient
       );

        $this->db->where($dataWhere);

        $myInsert = $this->db->update('mt_review', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }


    function DeleteToko_post(){

        $client_id = $this->post('client_id');
        $item_id = $this->post('item_id');



        $data = array(
         'client_id' => $client_id,
         'item_id' => $item_id
       );


        $this->db->where($data);
        $myDelete =  $this->db->delete('mt_review');

        if($myDelete){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }
    
    
    
     function AllProductSeller_post(){

        $merchant_id = $this->post('merchant_id');
        $datas = array('A.merchant_id' => $merchant_id);
        $this->db->select(' A.*');
        $this->db->from('mt_item A');
        $this->db->where($datas);
        $AllProductSeller = $this->db->get()->result();

        if($AllProductSeller){
            $this->response(array('status'=>'success','message'=>$AllProductSeller, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }
    
    
    function AddProductSeller_post(){

        $merchant_id = $this->post('merchant_id');
        $item_name = $this->post('item_name');
        $item_description = $this->post('item_description');
        $brand_id = $this->post('brand_id');
        $status = $this->post('status');
        $category = $this->post('category');
        $cat_main = $this->post('cat_main');
        $subsubcategory_id = $this->post('subsubcategory_id');
        $price = $this->post('price');
        $weight = $this->post('weight');
        $discount = $this->post('discount');
        $date_created = $this->post('date_created');
        $stock = $this->post('stock');
        $condition = $this->post('condition');
        $uom = $this->post('uom');
        $min_order = $this->post('min_order');
        $min_quantity = $this->post('min_quantity');
        $stream = $this->post('stream');
        
    
        $datas = array(
            'merchant_id' => $merchant_id,
            'item_name' => $item_name,
            'item_description' => $item_description,
            'brand_id' => $brand_id,
            'status' => $status,
            'category' => $category,
            'cat_main' => $cat_main,
            'subsubcategory_id' => $subsubcategory_id,
            'price' => $price,
            'weight' => $weight,
            'discount' => $discount,
            'date_created' => $date_created,
            'stock' => $stock,
            'uom' => $uom,
            'min_order' => $min_order,
            'min_quantity' => $min_quantity,
            'stream' => $stream
        );
        
      $myInsert = $this->db->insert('mt_item', $datas);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }
    
    
    function AllBrands_get(){

        $this->db->select(' A.*');
        $this->db->from('brand A');
        $AllBRANDS = $this->db->get()->result();

        if($AllBRANDS){
            $this->response(array('status'=>'success','message'=>$AllBRANDS, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }


    function AllCategory_get(){

        $this->db->select(' A.*');
        $this->db->from('mt_category A');
        $AllCategory = $this->db->get()->result();

        if($AllCategory){
            $this->response(array('status'=>'success','message'=>$AllCategory, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }

    function AllCategoryMain_get(){

        $this->db->select(' A.*');
        $this->db->from('mt_category_main A');
        $AllCategoryMain = $this->db->get()->result();

        if($AllCategoryMain){
            $this->response(array('status'=>'success','message'=>$AllCategoryMain, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }

    function AllItem_get(){

        $this->db->select(' A.*');
        $this->db->from('mt_item A');
        $AllItem = $this->db->get()->result();

        if($AllItem){
            $this->response(array('status'=>'success','message'=>$AllItem, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }

    function HistoryTransaksi_post(){

        $client_id = $this->post('client_id');
        $datas = array('A.client_id' => $client_id);
        $this->db->select('A.*');
        $this->db->from('mt_order A');
        
        $this->db->where($datas);
        $customer = $this->db->get()->result();

        if($customer){
            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }
    
    
     function HistoryTransaksiDetail_post(){

        $client_id = $this->post('order_id');
        $datas = array('A.order_id' => $client_id);
        $this->db->select('A.*');
        $this->db->from('mt_order_details A');
        
        $this->db->where($datas);
        $customer = $this->db->get()->result();

        if($customer){
            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>NULL, 'code' => 502));
        }
    }
    
            function AddRekening_post(){

        $norek = $this->post('norek');
        $namarek = $this->post('namarek');
        $namanasabah = $this->post('namanasabah');
        $client_id = $this->post('client_id');


        $data = array(
         'norek' => $norek,
         'namarek' => $namarek,
         'namanasabah' => $namanasabah,
         'client_id' => $client_id
     );

        $myInsert = $this->db->insert('mt_rekening', $data);

        if($myInsert){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'failed','message' => null, 'code' => 502));
        }
    }
    
    function UpdateRekening_post(){

        $norek = $this->post('norek');
        $namarek = $this->post('namarek');
        $namanasabah = $this->post('namanasabah');
        $client_id = $this->post('client_id');
        
        $data = array(
           'norek' => $norek,
           'namarek' => $namarek,
           'namanasabah' => $namanasabah,
           'client_id' => $client_id
       );

        $this->db->where('client_id', $client_id);
        $myInsert = $this->db->update('mt_rekening', $data);

        if($myInsert){

            $datas = array('client_id' => $client_id);

            $this->db->select('*');
            $this->db->from('mt_rekening');
            $this->db->where($datas);

            $customer = $this->db->get()->result();



            $this->response(array('status'=>'success','message'=>$customer, 'code' => 200));

        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }

    function RemoveRekening_post(){

        $norek = $this->post('norek');
        $namarek = $this->post('namarek');
        $namanasabah = $this->post('namanasabah');
        $client_id = $this->post('client_id');

        $data = array(
            'norek' => $norek,
            'namarek' => $namarek,
            'namanasabah' => $namanasabah,
            'client_id' => $client_id
       );


        $this->db->where($data);
        $myDelete =  $this->db->delete('mt_rekening');

        if($myDelete){
            $this->response(array('status'=>'success','message'=>NULL, 'code' => 200));
        }else{
            $this->response(array('status' => 'fail','message' => null, 'code' => 502));
        }
    }

    function ViewRekening_post (){

        $data = array('A.client_id' =>$this->post('client_id'));

        $this->db->select('
            A.norek, A.namarek, A.namanasabah, A.client_id,
            B.client_id, B.email 
            ');

        $this->db->from('mt_rekening A');
        $this->db->join('mt_client B', 'A.`client_id` = B.client_id', 'right');
        $this->db->where($data);
        $myData = $this->db->get()->result();

        if($myData){
            $this->response(array('status'=>'success','message'=>$myData, 'code' => 200));
        }else{
            $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
        }
    }

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
             $this->response(array('code'=>502,'data'=>null, 'message' => 'Document Tersedia'));
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
             $this->response(array('code'=>502,'data'=>null, 'message' => 'Document Tersedia'));
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
                $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
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
                $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
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
                $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
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
                $this->response(array('status'=>'failed','message'=>null, 'code' => 502));
            }
    }   
}

?>