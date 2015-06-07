<?php
use PHPPE\Core as PHPPE;

echo("libgd: ");
if(!function_exists("imagepng")) {
	echo("Failed\n");
	return false;
} else echo ("OK\n");

echo("test image exists: ");
if( !file_exists("vendor/phppe/core/images/phppe.png") ) {
	echo("Not found\n");
} else {
	echo("OK\n");

	echo("thumbnail (png, 64x64): ");
	PHPPE::picture("vendor/phppe/core/images/phppe.png","data/test1.png",64,64,true,true,"",64);
	$im = imagecreatefrompng("data/test1.png");
	if( !file_exists("data/test1.png") || filesize("data/test1.png")>65535 || imagesx($im)!=64 || imagesy($im)!=64) {
		echo("Failed\n");
		@unlink("data/test1.png");
		return false;
	} else echo("OK\n");
	unlink("data/test1.png");

	echo("large (jpeg, 1024x640): ");
	PHPPE::picture("vendor/phppe/core/images/phppe.png","data/test1.jpg",1024,768,false,false,"",512);
	$im = imagecreatefromjpeg("data/test1.jpg");
	if( !file_exists("data/test1.jpg") || filesize("data/test1.jpg")>512*1024 || imagesx($im)!=1024 || imagesy($im)!=640 ) {
		echo("Failed\n");
		@unlink("data/test1.jpg");
		return false;
	} else echo("OK\n");
	unlink("data/test1.jpg");
}

//portait image
$im = imagecreatetruecolor( 128,512 );
$c=0;
for($y=0;$y<imagesy($im);$y+=floor(imagesy($im)/255))
	imagefilledrectangle($im, 0, $y, imagesx($im),$y+floor(imagesy($im)/255),imagecolorallocate($im,$c,$c,$c++));
imagepng($im,"data/test1.png",9);

echo("portrait crop: ");
PHPPE::picture("data/test1.png","data/test2.png",128,64,true);
$im = imagecreatefrompng("data/test2.png");
if( !$im || imagesx($im)!=128 || imagesy($im)!=64 ) {
	echo("Failed\n");
	@unlink("data/test1.png");
	@unlink("data/test2.png");
	return false;
} else echo("OK\n");

echo("portrait resize: ");
PHPPE::picture("data/test1.png","data/test2.png",1024,1024,false);
$im = imagecreatefrompng("data/test2.png");
if( !$im || imagesx($im)!=256 || imagesy($im)!=1024 ) {
	echo("Failed\n");
	@unlink("data/test1.png");
	@unlink("data/test2.png");
	return false;
} else echo("OK\n");

//landscape image
$im = imagecreatetruecolor( 512,128 );
$c=0;
for($x=0;$x<imagesx($im);$x+=floor(imagesx($im)/255))
	imagefilledrectangle($im, $x, 0, $x+floor(imagesx($im)/255),imagesy($im),imagecolorallocate($im,$c,$c,$c++));
imagepng($im,"data/test1.png",9);

echo("landscape crop: ");
PHPPE::picture("data/test1.png","data/test2.png",64,128,true);
$im = imagecreatefrompng("data/test2.png");
if( !$im || imagesx($im)!=64 || imagesy($im)!=128 ) {
	echo("Failed\n");
	@unlink("data/test1.png");
	@unlink("data/test2.png");
	return false;
} else echo("OK\n");

echo("landscape resize: ");
PHPPE::picture("data/test1.png","data/test2.png",1024,1024,false);
$im = imagecreatefrompng("data/test2.png");
if( !$im || imagesx($im)!=1024 || imagesy($im)!=256 ) {
	echo("Failed\n");
	@unlink("data/test1.png");
	@unlink("data/test2.png");
	return false;
} else echo("OK\n");

unlink("data/test1.png");
unlink("data/test2.png");

return true;
?>