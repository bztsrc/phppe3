<?php
use PHPPE\Core as PHPPE;

//http helpers test

echo("Permanent url: ".url()." ");
if( url() != PHPPE::url() || basename(dirname(url())) != "tests") {
    echo("this should never happen!\n");
    return false;
} else echo ("OK\n");

echo("Application override: ".url("a")." ");
if( basename(url("a")) != "a" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("Action override: ".url("a","b")." ");
if( basename(dirname(url("a","b"))) != "a" || basename(url("a","b")) != "b" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Get: ");
echo(PHPPE::get(url("","http")));
if( PHPPE::get(url("","http")) != "OK " ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Post: ");
if( PHPPE::get(url("","http")."post",array("var1"=>"test1","var2"=>"test2")) != '{"var1":"test1","var2":"test2"}' ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Timeout: ");
if( PHPPE::get(url("","http")."timeout",null,1) != "" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Redirect: ");
if( PHPPE::get(url("","http")."redirect") != "Redirected" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Cookie change: ");
if( PHPPE::get(url("","http")."cookie") != "OK" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP Language: ");
PHPPE::$client->lang="xx";
if( PHPPE::get(url("","http")."language") != "xx;q=0.8" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

echo("HTTP CR removal: ");
if( PHPPE::get(url("","http")."cr1") != "CR" || PHPPE::get(url("","http")."cr2") != "C\rR" ) {
    echo("failed!\n");
    return false;
} else echo ("OK\n");

//everything was ok
return true;
?>