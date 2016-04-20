<?php
/**
 * Helper to choose JS assets
 */
if(!empty($_REQUEST['assetn'])){
	$d=explode("/",trim($_REQUEST['assetn']))[0];
	header("Content-type:text/javascript;charset=utf-8");
	die(file_get_contents(".tmp/".session_id()."/js/".$d));
}