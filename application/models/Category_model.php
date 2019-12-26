<?php
class Category_model extends MY_Model {
    public $_table = 'mt_category';
    public $primary_key = 'cat_id';

    public function getById($main_id) {
        $this->db->where('main_id', $main_id);
        return $this->db->get($this->_table);
    }


}


?>