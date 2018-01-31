<?php
/**
 * Controller for core deployment formatting
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Repository;
use PHPPE\Assets;

class MinifyController {
	static $cli="minify";

	function action()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");
		if(empty($_SERVER['argv'][2])){
			//! convert source to deployment format
			Repository::compress();
			//! update document
			Repository::updateDoc();
		} else {
			Core::$core->nominify=false;
			$data=@file_get_contents($_SERVER['argv'][2]);
			if(empty($data))
				die(chr(27)."[91munable to read ".$_SERVER['argv'][2].chr(27)."[0m\n");
			$i=strpos($data,"*/")+2;
			$out=substr($data,0,$i)."\n".Assets::minify(substr($data,$i),"php");
			$f=!empty($_SERVER['argv'][3])?$_SERVER['argv'][3]:$_SERVER['argv'][2];
			echo("  ".sprintf("%-40s ".chr(27)."[90m%6d",substr($f,-40), strlen($data)));
			if(!file_put_contents($f,$out))
				die(sprintf(" %6d ",0).chr(27)."[91m".L("FAIL").chr(27)."[0m\n");
			die(sprintf(" %6d ",strlen($out)).chr(27)."[92m".L("OK").chr(27)."[0m\n");
		}
	}

}
?>
