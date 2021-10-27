<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/
require 'common.inc.php';
$title = 'PHPLite @ UNN.tech';

$a = array('loop 1','loop 2','loop 3');

$ga = new \LiteClass\GoogleAuthenticator(); //谷歌验证器示例
$secret = $ga->createSecret();
$qrCodeUrl = $ga->getQRCodeGoogleUrl('phplite.unn.tech', $secret, 'PHPLite @ UNN.tech '); //第一个参数是"标识",第二个参数为"安全密匙SecretKey" 生成二维码信息
$PHPLite->alog('VISIT',$DT_IP,date('Y-m-d H:i:s',$DT_TIME));

/*-------
$wx = new \LiteClass\wx_class();
$signPackage = $wx->getSignPackage() ;
------*/


include template('index');
?>