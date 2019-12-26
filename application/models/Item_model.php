<?php
class Item_model extends MY_Model {
    public $_table = 'mt_item';
    public $primary_key = 'item_id';

    public function getById($merchant_id, $type_sell) {
        $this->db->join('mt_category_main', 'mt_category_main.main_id=mt_item.cat_main');
        $this->db->join('mt_category', 'mt_category.cat_id=mt_item.category');
        $this->db->where('merchant_id', $merchant_id);
        $this->db->where('type_sell', $type_sell);
        return $this->db->get($this->_table);
    }

}
?>