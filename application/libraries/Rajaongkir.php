<?php
/**
 * RajaOngkir PHP Client
 * 
 * Class PHP untuk mengkonsumsi API RajaOngkir
 * Berdasarkan dokumentasi RajaOngkir http://rajaongkir.com/dokumentasi
 * 
 * 
 */

class RajaOngkir {

    private $CI;

    private $_config = array();

    private static $api_key;
    private static $base_url = "https://api.rajaongkir.com/";
    private static $account_type;

    /**
     * Constructor
     * @param string $api_key API Key Anda sebagaimana yang tercantum di akun panel RajaOngkir
     * @param array $additional_headers Header tambahan seperti android-key, ios-key, dll
     */
    public function __construct() {
        log_message('debug', 'Rajaongkir: library initialized');
        $this->CI =& get_instance();
        $this->_config = $this->CI->config->item('rajaongkir');
        RajaOngkir::$api_key = $this->_config['key'];
        RajaOngkir::$account_type = $this->_config['account_type'];
        // \Unirest::defaultHeader("Content-Type", "application/x-www-form-urlencoded");
        Unirest\Request::defaultHeader("key", RajaOngkir::$api_key);
    }

    /**
     * Fungsi untuk mendapatkan data propinsi di Indonesia
     * @param integer $province_id ID propinsi, jika NULL tampilkan semua propinsi
     * @return object Object yang berisi informasi response, terdiri dari: code, headers, body, raw_body.
     */
    function getProvince($province_id = NULL) {
        $params = (is_null($province_id)) ? NULL : array('id' => $province_id);
        return Unirest\Request::get(RajaOngkir::$base_url. RajaOngkir::$account_type . "/province", array(), $params);
    }

    /**
     * Fungsi untuk mendapatkan data kota di Indonesia
     * @param integer $province_id ID propinsi
     * @param integer $city_id ID kota, jika ID propinsi dan kota NULL maka tampilkan semua kota
     * @return object Object yang berisi informasi response, terdiri dari: code, headers, body, raw_body.
     */
    function getCity($province_id = NULL, $city_id = NULL) {
        $params = (is_null($province_id)) ? NULL : array('province' => $province_id);
        if (!is_null($city_id)) {
            $params['id'] = $city_id;
        }
        return Unirest\Request::get(RajaOngkir::$base_url.  RajaOngkir::$account_type . "/city", array(), $params);
    }

    /**
     * Fungsi untuk mendapatkan data ongkos kirim
     * @param integer $origin ID kota asal
     * @param integer $destination ID kota tujuan
     * @param integer $weight Berat kiriman dalam gram
     * @param string $courier Kode kurir, jika NULL maka tampilkan semua kurir
     * @return object Object yang berisi informasi response, terdiri dari: code, headers, body, raw_body.
     */
    function getCost($origin, $destination, $weight, $courier = NULL) {
        $params = array(
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight
        );
        if (!is_null($courier)) {
            $params['courier'] = $courier;
        }
        return Unirest\Request::post(RajaOngkir::$base_url . RajaOngkir::$account_type . "/cost", array(), http_build_query($params));
    }

}
