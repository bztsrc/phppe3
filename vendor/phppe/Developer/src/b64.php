<?php
/**
 * Helper script to generate data urls
 */
$a=$_SERVER['argv'];
array_shift($a);
if(empty($a))
	die("Converts files to inlined data\n\n".$_SERVER['argv'][0]." file1 [file2 ...]\n");
foreach($a as $v)
	if(file_exists($v))
		echo($v.":\ndata:".mime_content_type($v).";base64,".base64_encode(file_get_contents($v))."\n\n");
	else
		echo($v.": no such file!!!\n");
?>
