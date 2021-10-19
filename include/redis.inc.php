<?php
$redis = new redis();
$redis->connect( $CFG['db_host'], 6379 );
//$redis->auth("");
//$redis->select(6); 

?>