<?php 
class Logistik extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        parent::loadModel('Logistik_model');
    }
    // /**
    //  * contoh override method 
    //  */
    // public function all_get() {
    //     parent::needAuth(); // this will make only current endpoint need token
    //     parent::all_get();
    // }

}
?>