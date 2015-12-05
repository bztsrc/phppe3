<?php
use PHPPE\Core as PHPPE;

// cache test

//check if it's configured and initialized
echo("Cache configured: ");
if( empty(PHPPE::$core->cache) ) {
	echo("cache not configured!\n");
	return "SKIP";
} else echo("OK\n");

echo("Cache initialized");
if( empty(PHPPE::mc()) ) {
	echo(": cache not initialized!\n");
	return false;
} else echo(" (".get_class(PHPPE::mc())."): OK\n");

//basic get/set
$mc = PHPPE::mc();
$var = "t_00".time();

echo("Set value: ");
if( !$mc->set($var,"aaa") ) {
	echo("failed to set!\n");
	return false;
} else echo("OK\n");

echo("Get value: ");
if( $mc->get($var)!="aaa" ) {
	echo("failed to get!\n");
	return false;
} else echo("OK\n");

//value time to live
echo("Cache TTL: ");
//make sure to empty template cache
$tn = 't_' . sha1(PHPPE::$core->base."_cachetest");
$mc->set($tn,"",false,1);
$mc->set($var,"bbb",false,1);
sleep(1);
if(method_exists($mc,"cleanUp")) $mc->cleanUp();
//this check always fail on APC, because it's cleared on next request only...
if( $mc->get($var) ) {
	if(PHPPE::$core->cache!="apc") {
	    echo("failed to get!\n");
	    return false;
	} else echo("SKIPPED\n");
} else echo("OK\n");

//raw view template should be cached always
echo("Template caching: ");
if( $mc->get($tn) ) {
	echo("template still in cache!\n");
	return false;
}
$txt = PHPPE::template("cachetest");
if( !$mc->get($tn) ) {
	echo("template not in cache!\n");
	return false;
} else echo("OK\n");

echo("Output caching: ");
PHPPE::get(url("","http")."cachetest"); //make sure the output gets to the cache
$d1 = PHPPE::get(url("","http")."cachetest"); //this must be served from cache
$d2 = PHPPE::get(url("","http")."cachetest?skipcache=1"); //trigger nocache flag set in constructor
if( strpos($d1,", mc -->")===false || strpos($d1,"NOCACHE")!==false ||
    strpos($d2,", mc -->")===false || strpos($d2,"NOCACHE")===false ) {
	echo("application output cache control failed!\n");
	return false;
} else echo("OK\n");

if(method_exists($mc,"cleanUp")) $mc->cleanUp();

//everything was ok
return true;
?>