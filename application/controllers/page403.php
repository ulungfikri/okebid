<?php
use Restserver\Libraries\REST_Controller;

    class page403 extends REST_Controller {


        public function index_get() {
            $this->response(null, 403);
        }
    }
?>