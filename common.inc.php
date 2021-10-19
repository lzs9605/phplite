<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/

define('DT_DEBUG', 1);
if(DT_DEBUG) {
	error_reporting(E_ALL);
	$mtime = explode(' ', microtime());
	$debug_starttime = $mtime[1] + $mtime[0];
} else {
	error_reporting(0);
}

if(isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) exit('Request Denied');
define('IN_PHPLite', true);
define('DT_ROOT', str_replace("\\", '/', dirname(__FILE__)));
$CFG = array();
require DT_ROOT.'/config.inc.php';
define('DT_DOMAIN', $CFG['cookie_domain'] ? substr($CFG['cookie_domain'], 1) : '');
define('DT_WIN', strpos(strtoupper(PHP_OS), 'WIN') !== false ? true: false);
define('DT_CHMOD', ($CFG['file_mod'] && !DT_WIN) ? $CFG['file_mod'] : 0);
define('DT_KEY', $CFG['authkey']);
define('DT_CACHE', $CFG['cache_dir'] ? $CFG['cache_dir'] : DT_ROOT.'/file/cache');
define('DT_SKIN', 'skin/'.$CFG['skin'].'/');
define('errmsg', 'Invalid Request');

require_once DT_ROOT . '/vendor/autoload.php';  //composer autoload，如不需要也可不载入
require DT_ROOT.'/version.inc.php';
require DT_ROOT.'/include/global.func.php';
require DT_ROOT.'/include/file.func.php';

$DT_TIME = time() + $CFG['timediff'];
$DT_BOT = is_robot();
$DT_IP = get_env('ip');
$DT_TOUCH = is_touch();

header("Content-Type:text/html;charset=".'UTF-8');
if($CFG['db_host'] != ''){
    require DT_ROOT.'/include/db_'.$CFG['database'].'.class.php';
    $db_class = 'db_'.$CFG['database'];
    $db = new $db_class;
    $db->pre = $CFG['tb_pre'];
    $db->connect($CFG['db_host'], $CFG['db_user'], $CFG['db_pass'], $CFG['db_name'], $CFG['db_expires'], $CFG['db_charset'], $CFG['pconnect']);
}

$PHPLite = new \LiteClass\phplite_class();


//  以下更据需要加载扩展
//require_once DT_ROOT.'/include/redis.inc.php';


