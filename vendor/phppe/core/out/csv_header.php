<?php
//get current application. We are about to output it's properties
$app = \PHPPE\Core::getval("app");

//write a comment with query to output
if( !empty($app->query) )
	echo("## ".strtr($app->query,array("\r"=>"","\n"=>" "))."\n");

if( empty($app->results) )
	die("CSV-C: ".L("No input"));

#print header
foreach($app->results[0] as $k=>$v)
	echo(($k==array_keys($app->results[0])[0]?"":";")."\"".addslashes($k)."\"");
echo("\n");

//print data
foreach($app->results as $V) {
	foreach($V as $k=>$v)
		echo(($k==array_keys($V)[0]?"":";")."\"".addslashes($v)."\"");
	echo("\n");
}

//view layer not required at all in this case
exit(0);
?>
