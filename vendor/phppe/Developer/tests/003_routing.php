<?php
use PHPPE\Core as PHPPE;

// route test
$routes = PHPPE::route();

//check if it's empty
echo("Routing exists: ");
if( empty($routes[sha1("tests|\Tests|")]) ) {
	echo("route to this page doesn't exists?!\n");
	return false;
} else echo("OK\n");

//add different new routes
PHPPE::route("test1","Tests");
PHPPE::route("test2","Tests","action_run");
PHPPE::route(array("url"=>"test3","name"=>"Tests","action"=>"action_run"));
PHPPE::route(array(
	array("test4","Tests","action_run"),
	array("test5","Tests","action_run")
));
$r = new \stdClass();
$r->url="test6";
$r->name="Tests";
PHPPE::route($r);
PHPPE::route("test7","Tests","action_member","@loggedin,admin");
PHPPE::route("test7","Tests","action_public");
PHPPE::route("test9","Tests","",array("@loggedin","admin"));

//get routes again
$new = PHPPE::route();

//checks
echo("New class route: ");
if( count($routes) == count($new) ) {
	echo("failed to add!\n");
	return false;
} else echo("OK\n");
echo("New action route: ");
if( !empty($new[sha1("test1|Tests|")][2]) ) {
	echo("failed to add default action route!\n");
	return false;
} else echo("OK\n");
echo("New named route: ");
if( empty($new[sha1("test2|Tests|action_run")][1]) ) {
	echo("failed to add named action!\n");
	return false;
} else echo("OK\n");
echo("New assoc array route: ");
if( empty($new[sha1("test3|Tests|action_run")][1]) ) {
	echo("failed to add from assoc array!\n");
	return false;
} else echo("OK\n");
echo("New multiple routes: ");
if( empty($new[sha1("test4|Tests|action_run")][2]) || empty($new[sha1("test5|Tests|action_run")][2]) ) {
	echo("failed to add multiple routes at once!\n");
	return false;
} else echo("OK\n");
echo("New object route: ");
if( empty($new[sha1("test6|Tests|")][0]) ) {
	echo("failed to add from object!\n");
	return false;
} else echo("OK\n");
echo("Same route with and without filter: ");
if( $new[sha1("test7|Tests|action_member")][0] != $new[sha1("test7|Tests|action_public")][0] ) {
	echo("failed to add!\n");
	return false;
} else echo("OK\n");
echo("Filter by string and by array: ");
if( serialize($new[sha1("test7|Tests|action_member")][3]) != serialize($new[sha1("test9|Tests|")][3]) ) {
	echo("failed to add!\n");
	return false;
} else echo("OK\n");
echo("Get filter: ");
if( PHPPE::get(url("","httptest")) != "GET" ) {
	echo("failed to filter route by HTTP GET!\n");
	return false;
} else echo("OK\n");

echo("Post filter: ");
if( PHPPE::get(url("","httptest"),["par1"=>"var1","par2"=>"var2"]) != "POST" ) {
	echo("failed to filter route by HTTP POST!\n");
	return false;
} else echo("OK\n");

//everything was ok
return true;
?>