<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/

namespace LiteClass ;
    
class phplite_class {
    protected $db, $DT_TIME;
	public $e;

    public function __construct() {
        global $db, $DT_TIME;
        $this->db = $db;
        $this->DT_TIME = $DT_TIME;
    }

    public function __destruct() {

    }

    public function alog( $log1, $log2 = '', $log3 = '' ) {
        $SQLC = "INSERT INTO alog (log1,log2,log3) VALUES ('" . addslashes( $log1 ) . "','" . addslashes( $log2 ) . "','" . addslashes( $log3 ) . "')";
        $this->db->query( $SQLC );
    }

    public function is_weixin() {
        if ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MicroMessenger' ) !== false ) {
            return true;
        }
        return false;
    }

    public function is_alipay() {
        if ( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Alipay' ) !== false ) {
            return true;
        }
        return false;
    }

    public function is_unionpay() {
        if ( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'UnionPay' ) !== false ) {
            return true;
        }
        return false;
    }

    public function check_client() {
        //0:未知或PC 1:微信 2：支付宝 3：云闪付
        if ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MicroMessenger' ) !== false ) {
            return 1;
        } elseif ( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Alipay' ) !== false ) {
            return 2;
        } elseif ( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'UnionPay' ) !== false ) {
            return 3;
        } else {
            return 0;
        }
    }
    
    public function getRequestHeaders(){
        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $header = [];
            $server = $_SERVER;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }

        $ret = array_change_key_case($header);

        return $ret;
    }
	
	public function createNonceStr( $length = 16 ) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ( $i = 0; $i < $length; $i++ ) {
            $str .= substr( $chars, mt_rand( 0, strlen( $chars ) - 1 ), 1 );
        }
        return $str;
    }
	
	public function do_get( $url, $aHeader = null) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        if($aHeader != null){
            foreach($aHeader as $k=>$v){
                $pHeader[] = "{$k}: {$v}";
            }
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $pHeader);
        }
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0' );
        $strRes = curl_exec( $ch );
        curl_close( $ch );
        return $strRes;
    }

    public function do_post( $url, $data, $aHeader = null ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        if($aHeader != null){
            foreach($aHeader as $k=>$v){
                $pHeader[] = "{$k}: {$v}";
            }
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $pHeader);
        }
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0' );
        $strRes = curl_exec( $ch );
        curl_close( $ch );
        return $strRes;
    }
	
	public function url2https($url){
		//return str_replace("http://","https://",$url);
		return preg_replace('/^http:/i','https:',$url);
	}
	
}


?>