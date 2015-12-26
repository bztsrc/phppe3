<?php
if(!empty($_REQUEST['asset'])){
	$d=explode("/",trim($_REQUEST['asset']))[0];
	header("Content-type:text/css;charset=utf-8");
	die(file_get_contents(".tmp/".session_id()."/css/".$d));
}