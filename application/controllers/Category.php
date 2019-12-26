<?php 
class Category extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        parent::loadModel('Category_model');
        $this->load->model("MainCategory_model", "maincategory");
        $this->load->model("Category_model", "category");
    }
    /**
     * contoh override method 
     */
    public function all_get() {
        parent::needAuth(); // this will make only current endpoint need token
        parent::all_get();
    }

    public function main_get() {
        $ara= $this->maincategory->get_all();
        $response = array(
            'status'=> 'ok',
            'result' => $ara
        );
        $this->response($response, parent::HTTP_OK);
    }

    public function sub_main_post() {
        $main_id = $this->input->post('main_id');        
        $ara= $this->category->getById($main_id);
        if ($ara->num_rows()>0) {
            $response = array(
                'status'=> 'ok',
                'result' => $ara->result()
            );
        }else {
            $response = array(
                'status'=> 'NG',
                
            );
        }
        
        $this->response($response, parent::HTTP_OK);
    }

}
?>