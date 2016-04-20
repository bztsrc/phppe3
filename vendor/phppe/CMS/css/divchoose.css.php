<?php
/**
 * Helper to choose CSS assets
 */
if(!empty($_REQUEST['assetn'])){
	$d=explode("/",trim($_REQUEST['assetn']))[0];
	header("Content-type:text/css;charset=utf-8");
	die((".tmp/".session_id()."/css/".$d));
}