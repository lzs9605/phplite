<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/
defined('IN_PHPLite') or exit('Access Denied');

function unn_encrypt($txt, $key = '', $expiry = 0) {
	strlen($key) > 5 or $key = DT_KEY;
	$str = $txt.substr($key, 0, 3);
	return str_replace(array('+', '/', '0x', '0X'), array('-P-', '-S-', '-Z-', '-X-'), unn_mycrypt($str, $key, 'ENCODE', $expiry));
}

function unn_decrypt($txt, $key = '') {
	strlen($key) > 5 or $key = DT_KEY;
	$str = unn_mycrypt(str_replace(array('-P-', '-S-', '-Z-', '-X-'), array('+', '/', '0x', '0X'), $txt), $key, 'DECODE');
	return substr($str, -3) == substr($key, 0, 3) ? substr($str, 0, -3) : '';
}

function unn_mycrypt($string, $key, $operation = 'DECODE', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + $GLOBALS['DT_TIME'] : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - $GLOBALS['DT_TIME'] > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

function template($template = 'index', $dir = '') {
	global $CFG;
	check_name($template) or exit('BAD TPL NAME');
	if($dir) check_name($dir) or exit('BAD TPL DIR');
	$to = DT_CACHE.'/tpl/'.$CFG['template'].'/'.($dir ? $dir.'/' : '').$template.'.php';
	$isfileto = is_file($to);
	if($CFG['template_refresh'] || !$isfileto) {
		if($dir) $dir = $dir.'/';
        $from = DT_ROOT.'/template/'.$CFG['template'].'/'.$dir.$template.'.htm';
		if($CFG['template'] != 'default' && !is_file($from)) {
			$from = DT_ROOT.'/template/default/'.$dir.$template.'.htm';
		}
        if(!$isfileto || filemtime($from) > filemtime($to) || (filesize($to) == 0 && filesize($from) > 0)) {
			require_once DT_ROOT.'/include/template.func.php';
			template_compile($from, $to);
		}
	}
	return $to;
}

function ob_template($template, $dir = '') {
	extract($GLOBALS, EXTR_SKIP);
	ob_start();
	include template($template, $dir);
	$contents = ob_get_contents();
	ob_clean();
	return $contents;
}

function check_name($username) {
	if(strpos($username, '__') !== false || strpos($username, '--') !== false) return false; 
	return preg_match("/^[a-z0-9]{1}[a-z0-9_\-]{0,}[a-z0-9]{1}$/", $username);
}

function random($length, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++)	{
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function set_cookie($var, $value = '', $time = 0) {
	global $CFG, $DT_TIME;
	$time = $time > 0 ? $time : (empty($value) ? $DT_TIME - 3600 : 0);
	$port = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
	$var = $CFG['cookie_pre'].$var;
	return setcookie($var, $value, $time, $CFG['cookie_path'], $CFG['cookie_domain'], $port);
}

function get_cookie($var) {
	global $CFG;
	$var = $CFG['cookie_pre'].$var;
	return isset($_COOKIE[$var]) ? $_COOKIE[$var] : '';
}

function get_process($fromtime, $totime) {
	global $DT_TIME;
	if($fromtime && $DT_TIME < $fromtime) return 1;
	if($totime && $DT_TIME > $totime) return 3;
	return 2;
}

function cache_read($file, $dir = '', $mode = '') {
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	if(!is_file($file)) return $mode ? '' : array();
	return $mode ? file_get($file) : include $file;
}

function cache_write($file, $string, $dir = '') {
	if(is_array($string)) $string = "<?php defined('IN_PHPLite') or exit('Access Denied'); return ".strip_nr(var_export($string, true))."; ?>";
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	$strlen = file_put($file, $string);
	return $strlen;
}

function cache_delete($file, $dir = '') {
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	return file_del($file);
}

function cache_clear($str, $type = '', $dir = '') {
	$dir = $dir ? DT_CACHE.'/'.$dir.'/' : DT_CACHE.'/';
	$files = glob($dir.'*');
	if(is_array($files)) {
		if($type == 'dir') {
			foreach($files as $file) {
				if(is_dir($file)) {dir_delete($file);} else {if(file_ext($file) == $str) file_del($file);}
			}
		} else {
			foreach($files as $file) {
				if(!is_dir($file) && strpos(basename($file), $str) !== false) file_del($file);
			}
		}
	}
}


function get_env($type) {
	switch($type) {
		case 'ip':
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				if(is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
				$ip = trim(end(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
				if(is_ip($ip)) return $ip;
			}
			if(isset($_SERVER['REMOTE_ADDR']) && is_ip($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
			if(isset($_SERVER['HTTP_CLIENT_IP']) && is_ip($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
			return '0.0.0.0';
		break;
		case 'self':
			return isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['ORIG_PATH_INFO']);
		break;
		case 'referer':
			return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		break;
		case 'domain':
			return $_SERVER['SERVER_NAME'];
		break;
		case 'scheme':
			return $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		break;
		case 'port':
			return ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : ':'.$_SERVER['SERVER_PORT'];
		break;
		case 'host':
			return preg_match("/^[a-z0-9_\-\.]{4,}$/i", $_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		break;
		case 'url':
			if(isset($_SERVER['HTTP_X_REWRITE_URL']) && $_SERVER['HTTP_X_REWRITE_URL']) {
				$uri = $_SERVER['HTTP_X_REWRITE_URL'];
			} else if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $_SERVER['PHP_SELF'];
				if(isset($_SERVER['argv'])) {
					if(isset($_SERVER['argv'][0])) $uri .= '?'.$_SERVER['argv'][0];
				} else {
					$uri .= '?'.$_SERVER['QUERY_STRING'];
				}
			}
			$uri = dhtmlspecialchars($uri);
			return get_env('scheme').$_SERVER['HTTP_HOST'].(strpos($_SERVER['HTTP_HOST'], ':') === false ? get_env('port') : '').$uri;
		break;
		case 'mobile':
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$ck = get_cookie('mobile');
			$os = $browser = '';
			if(strpos($ua, 'android') !== false) {
				$os = 'android';
				if($ck == 'app') {
					$browser = 'app';
				} else if($ck == 'b2b') {
					$browser = 'b2b';
				} else {
					if(strpos($ua, 'micromessenger/') !== false) {
						$browser = 'weixin';
					} else if(strpos($ua, 'qq/') !== false) {
						$browser = 'qq';
					}
				}
			} else if(strpos($ua, 'iphone') !== false || strpos($ua, 'ipod') !== false) {
				$os = 'ios';
				if($ck == 'app') {
					$browser = 'app';
				} else if($ck == 'b2b') {
					$browser = 'b2b';
				} else if($ck == 'screen') {
					$browser = 'screen';
				} else {
					if(strpos($ua, 'micromessenger/') !== false) {
						$browser = 'weixin';
					} else if(strpos($ua, 'qq/') !== false) {
						$browser = 'qq';
					} else if(strpos($ua, 'safari') !== false) {
						$browser = 'safari';
					}
				}
			} else if(strpos($ua, 'adr') !== false && strpos($ua, 'ucbrowser') !== false) {
				$os = 'android';
				$browser = 'uc';
			}
			return array('os' => $os, 'browser' => $browser);
		break;
	}
}

function convert($str, $from = 'utf-8', $to = 'gb2312') {
	if(!$str) return '';
	$from = strtolower($from);
	$to = strtolower($to);
	if($from == $to) return $str;
	$from = str_replace('gbk', 'gb2312', $from);
	$to = str_replace('gbk', 'gb2312', $to);
	$from = str_replace('utf8', 'utf-8', $from);
	$to = str_replace('utf8', 'utf-8', $to);
	if($from == $to) return $str;
	$tmp = array();
	if(function_exists('mb_convert_encoding')) {
		if(is_array($str)) {
			foreach($str as $key => $val) {
				$tmp[$key] = mb_convert_encoding($val, $to, $from);
			}
			return $tmp;
		} else {
			return mb_convert_encoding($str, $to, $from);
		}
	} else if(function_exists('iconv')) {
		if(is_array($str)) {
			foreach($str as $key => $val) {
				$tmp[$key] = iconv($from, $to."//IGNORE", $val);
			}
			return $tmp;
		} else {
			return iconv($from, $to."//IGNORE", $str);
		}
	} else {
		require_once DT_ROOT.'/include/convert.func.php';
		return dconvert($str, $to, $from);
	}
}


function is_robot() {
	return preg_match("/(spider|bot|crawl|slurp|lycos|robozilla)/i", $_SERVER['HTTP_USER_AGENT']);
}

function is_ip($ip) {
	return preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $ip);
}

function is_mobile($mobile) {
	return preg_match("/^1[3|4|5|7|8]{1}[0-9]{9}$/", $mobile);
}

function is_md5($password) {
	return preg_match("/^[a-f0-9]{32}$/", $password);
}

function is_openid($openid) {
	return preg_match("/^[0-9a-zA-Z\-_]{10,}$/", $openid);
}

function is_touch() {
	$ck = get_cookie('mobile');
	if($ck == 'pc') return 0;
	if($ck == 'touch' || $ck == 'screen') return 1;
	return preg_match("/(iPhone|iPad|iPod|Android)/i", $_SERVER['HTTP_USER_AGENT']) ? 1 : 0;
}

function dhttp($status, $exit = 1) {
	switch($status) {
		case '301': @header("HTTP/1.1 301 Moved Permanently"); break;
		case '403': @header("HTTP/1.1 403 Forbidden"); break;
		case '404': @header("HTTP/1.1 404 Not Found"); break;
		case '503': @header("HTTP/1.1 503 Service Unavailable"); break;
	}
	if($exit) exit;
}

//Safe function

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		return array_map('dhtmlspecialchars', $string);
	} else {
		$string = htmlspecialchars($string, ENT_QUOTES, DT_CHARSET == 'GBK' ? 'GB2312' : 'UTF-8');
		return str_replace('&amp;', '&', $string);
	}
}

function dsafe($string, $type = 1) {
	if(is_array($string)) {
		return array_map('dsafe', $string);
	} else {
		if($type) {
			$string = str_replace('<em></em>', '', $string);
			$string = preg_replace("/\<\!\-\-([\s\S]*?)\-\-\>/", "", $string);
			$string = preg_replace("/\/\*([\s\S]*?)\*\//", "", $string);
			$string = preg_replace("/&#([a-z0-9]{1,})/i", "<em></em>&#\\1", $string);
			$match = array("/s[\s]*c[\s]*r[\s]*i[\s]*p[\s]*t/i","/d[\s]*a[\s]*t[\s]*a[\s]*\:/i","/b[\s]*a[\s]*s[\s]*e/i","/e[\\\]*x[\\\]*p[\\\]*r[\\\]*e[\\\]*s[\\\]*s[\\\]*i[\\\]*o[\\\]*n/i","/i[\\\]*m[\\\]*p[\\\]*o[\\\]*r[\\\]*t/i","/on([a-z]{2,})([\(|\=|\s]+)/i","/about/i","/frame/i","/link/i","/meta/i","/textarea/i","/eval/i","/alert/i","/confirm/i","/prompt/i","/cookie/i","/document/i","/newline/i","/colon/i","/<style/i","/\\\x/i");
			$replace = array("s<em></em>cript","da<em></em>ta:","ba<em></em>se","ex<em></em>pression","im<em></em>port","o<em></em>n\\1\\2","a<em></em>bout","f<em></em>rame","l<em></em>ink","me<em></em>ta","text<em></em>area","e<em></em>val","a<em></em>lert","/con<em></em>firm/i","prom<em></em>pt","coo<em></em>kie","docu<em></em>ment","new<em></em>line","co<em></em>lon","<sty1e","\<em></em>x");
			return str_replace(array('isShowa<em></em>bout', 'co<em></em>ntrols'), array('isShowAbout', 'controls'), preg_replace($match, $replace, $string));
		} else {
			return str_replace(array('<em></em>', '<sty1e'), array('', '<style'), $string);
		}
	}
}

function strip_sql($string, $type = 1) {
	if(is_array($string)) {
		return array_map('strip_sql', $string);
	} else {
		if($type) {
			global $DT_PRE;
			$string = preg_replace("/\/\*([\s\S]*?)\*\//", "", $string);
			$string = preg_replace("/0x([a-f0-9]{2,})/i", '0&#120;\\1', $string);
			$string = preg_replace_callback("/(select|update|replace|delete|drop)([\s\S]*?)({$DT_PRE}|from)/i", 'strip_wd', $string);
			$string = preg_replace_callback("/(load_file|substring|substr|reverse|trim|space|left|right|mid|lpad|concat|concat_ws|make_set|ascii|bin|oct|hex|ord|char|conv)([^a-z]?)\(/i", 'strip_wd', $string);
			$string = preg_replace_callback("/(union|where|having|outfile|dumpfile|{$DT_PRE})/i", 'strip_wd', $string);
			return $string;
		} else {
			return str_replace(array('&#95;','&#100;','&#101;','&#103;','&#105;','&#109;','&#110;','&#112;','&#114;','&#115;','&#116;','&#118;','&#120;'), array('_','d','e','g','i','m','n','p','r','s','t','v','x'), $string);
		}
	}
}
?>