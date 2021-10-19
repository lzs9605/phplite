<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/
require 'common.inc.php';
$title = 'PHPLite @ UNN.tech';

$a = array('loop a','loop b','loop c');

$ga = new \LiteClass\GoogleAuthenticator();
$secret = $ga->createSecret();
$qrCodeUrl = $ga->getQRCodeGoogleUrl('phplite.unn.tech', $secret, 'PHPLite @ UNN.tech '); //第一个参数是"标识",第二个参数为"安全密匙SecretKey" 生成二维码信息
$PHPLite->alog('VISIT',$DT_IP,date('Y-m-d H:i:s',$DT_TIME));

include template('index');
?>