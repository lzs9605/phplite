<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2008-2016 www.unn.tech
	This is NOT a freeware, use is subject to license.txt
*/

class unn_ewt{
    protected $iv;
    protected $cipher;
    protected $ckey;
    public $tag;
    public function __construct($key = '', $cipher ='des-ecb'){
        $this -> tag = NULL;
        $this -> cipher = $cipher;
        $this -> ckey = $key;
		$ivlen = openssl_cipher_iv_length($this -> cipher);
        //$this -> iv = openssl_random_pseudo_bytes($ivlen);
		$this -> iv = $this->ivstr($key, $ivlen);
	}
	
	public function __destruct(){
		
	}
	
	public function ivstr($key, $ivlen){
		$str = md5($key);
		$str = str_pad($str,$ivlen,'=');
		return substr($str,0,$ivlen);
	}
    
    public function encrypt($plaintext, $key = ''){
        if($key == ''){
            $key = $this -> ckey;
        }
        $ciphertext = openssl_encrypt($plaintext, $this -> cipher, $key, 0, $this -> iv); //, $this -> tag
        return $ciphertext;
    }
    
    public function decrypt($ciphertext, $key = ''){
        if($key == ''){
            $key = $this -> ckey;
        }
        $original_plaintext = openssl_decrypt($ciphertext, $this -> cipher, $key, 0, $this -> iv); //, $this -> tag
        return $original_plaintext;
    }
	
	/**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    public static function base64UrlEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return bool|string
     */
    public static function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    
}

/*------ For Example  ------

$ewt = new unn_ewt('passwordkey','des-ecb');
$s = 'message to be encrypted.';
$e = $ewt -> encrypt($s);
var_dump($e);
$c = $ewt -> decrypt($e);
var_dump($c);

--------------*/


class unn_ejwt extends unn_ewt{
    protected $salt;
    public $err;
    
    public function __construct($key = '', $cipher ='des-ecb'){
        parent::__construct($key, $cipher);
        $this -> salt = substr(md5('UNN.TECH'.$key),3,16);
        $this -> err = 0;
    }
    
    public function jencrypt($arr, $key=''){
        if(is_array($arr)){
            $rt = $this -> encrypt(json_encode($arr), $key);
        }else{
            $rt = $this -> encrypt($arr, $key);
        }
        $this -> err = 0;
        return $rt ;
    }
    
    public function jdecrypt($ciphertext, $key = ''){
        $re = $this -> decrypt($ciphertext, $key);
        if($re===false){
            $this -> err = 1;  //解密失败
            return false;
        }else{
            $arr = json_decode($re,true);
            if(is_array($arr)){
                $this -> err = 0;
                return $arr;
            }else{
                $this -> err = 2;  //非数组
                return false;
            }
        }
    }
    
    public function getToken($arr, $key=''){
        if(is_array($arr)){
            $sign = $this->signature($arr);
            $arr['sign'] = $sign;
            $rt = $this -> encrypt(json_encode($arr), $key);
            $this -> err = 0;
            return $rt;
        }else{
            return false;
        }
    }
    
    public function verifyToken($Token, $key=''){
        $re = $this -> decrypt($Token, $key);
        if($re===false){
            $this -> err = 1;  //解密失败
            return false;
        }else{
            $arr = json_decode($re,true);
            if(is_array($arr)){
                $sign = $arr['sign'];
                unset($arr['sign']);
                $_sign = $this->signature($arr);
                if($sign != $_sign){
                    $this -> err = 2; //签名错，数据被篡改
                    return false;
                }

                //签发时间大于当前服务器时间验证失败
                if (isset($arr['iat']) && $arr['iat'] > time()){
                    $this -> err = 3; 
                    return false;
                }

                //过期时间小宇当前服务器时间验证失败
                if (isset($arr['exp']) && $arr['exp'] < time()){
                    $this -> err = 4; 
                    return false;
                }

                //该nbf时间之前不接收处理该Token
                if (isset($arr['nbf']) && $arr['nbf'] > time()){
                    $this -> err = 5; 
                    return false;
                }
                
                $this -> err = 0;
                return $arr;
            }else{
                $this -> err = 2;  //非数组
                return false;
            }
        }
        
    }
    
    public function signature($arr){
        
        return md5(http_build_query($arr).$this->salt);
        
    }
}


/*------ For Example  ------

$ejwt = new unn_ejwt('passwordkey','des-ecb');
$s = array('sub'=>'user','name'=>'John Doe','iat'=>time(),'exp'=>time()+7200);
$e = $ejwt -> jencrypt($s);
var_dump($e);
$c = $ejwt -> jdecrypt($e);
var_dump($c);

--------------*/


/*------ For Example  ------

$ejwt = new unn_ejwt('passwordkey','des-ecb');
$s = array('sub'=>'user','name'=>'John Doe','iat'=>time(),'exp'=>time()+7200);
$e = $ejwt -> getToken($s);
var_dump($e);
$c = $ejwt -> verifyToken($e);
var_dump($c);
echo $ejwt->err;

--------------*/

?>