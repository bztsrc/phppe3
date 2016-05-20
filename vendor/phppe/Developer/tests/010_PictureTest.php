<?php
class Picture extends PHPUnit_Framework_TestCase
{
	public function testPicture()
	{
		$testImg = "vendor/phppe/Developer/images/test.jpg";

		if( !function_exists("gd_info") ||
			!file_exists($testImg))
			$this->markTestSkipped();

		//! not image
		@unlink("data/test1.png");
		@unlink("data/test2.png");
		\PHPPE\View::picture("no/such/file","data/test1.png",64,64,true,true,"",64);
		$this->assertFileNotExists("data/test1.png","No file");
		\PHPPE\View::picture(__FILE__,"data/test1.png",64,64,true,true,"",64);
		$this->assertFileExists("data/test1.png","Bad file");
		unlink("data/test1.png");
		
		//! test image
		\PHPPE\View::picture($testImg,"data/test1.png",64,64,true,true,"",64);
		$this->assertFileExists("data/test1.png","Thumbnail");
		@$im = imagecreatefrompng("data/test1.png");
		$this->assertNotNull($im,"Format png");
		$this->assertEquals(imagesx($im),64,"Width");
		$this->assertEquals(imagesy($im),64,"Height");
		$this->assertLessThanOrEqual(65536,filesize("data/test1.png"),"Size");
		unlink("data/test1.png");

		\PHPPE\View::picture($testImg,"data/test1.jpg",1024,768,false,false,"",128);
		$this->assertFileExists("data/test1.jpg","Large");
		@$im = imagecreatefromjpeg("data/test1.jpg");
		$this->assertNotNull($im,"Format jpeg");
		$this->assertEquals(imagesx($im),432,"Width");
		$this->assertEquals(imagesy($im),768,"Height");
		$this->assertLessThanOrEqual(131072,filesize("data/test1.jpg"),"Size");
		unlink("data/test1.jpg");

		//! watermark
		\PHPPE\View::picture($testImg,"data/test1.jpg",1024,768,false,false,"vendor/phppe/Developer/preview",128);
		$this->assertFileExists("data/test1.jpg","Watermark #1");

		\PHPPE\View::picture($testImg,"data/test1.jpg",1024,768,false,false,"no/such/file",128);
		$this->assertFileExists("data/test1.jpg","Watermark #2");
		unlink("data/test1.jpg");

		//! portait
		$im = imagecreatetruecolor( 128,512 );
		$c=0;
		for($y=0;$y<imagesy($im);$y+=floor(imagesy($im)/255))
			imagefilledrectangle($im, 0, $y, imagesx($im),$y+floor(imagesy($im)/255),imagecolorallocate($im,$c,$c,$c++));
		imagepng($im,"data/test1.png",9);

		\PHPPE\View::picture("data/test1.png","data/test2.png",128,64,true);
		$this->assertFileExists("data/test2.png","Portait crop");
		@$im = imagecreatefrompng("data/test2.png");
		$this->assertNotNull($im,"Format png");
		$this->assertEquals(imagesx($im),128,"Width");
		$this->assertEquals(imagesy($im),64,"Height");

		\PHPPE\View::picture("data/test1.png","data/test2.png",1024,1024,false);
		$this->assertFileExists("data/test2.png","Portait resize");
		@$im = imagecreatefrompng("data/test2.png");
		$this->assertNotNull($im,"Format png");
		$this->assertEquals(imagesx($im),256,"Width");
		$this->assertEquals(imagesy($im),1024,"Height");
		unlink("data/test1.png");
		unlink("data/test2.png");

		//! landscape
		$im = imagecreatetruecolor( 512,128 );
		$c=0;
		for($x=0;$x<imagesx($im);$x+=floor(imagesx($im)/255))
			imagefilledrectangle($im, $x, 0, $x+floor(imagesx($im)/255),imagesy($im),imagecolorallocate($im,$c,$c,$c++));
		imagepng($im,"data/test1.png",9);

		\PHPPE\View::picture("data/test1.png","data/test2.png",64,128,true);
		$this->assertFileExists("data/test2.png","Landscape crop");
		@$im = imagecreatefrompng("data/test2.png");
		$this->assertNotNull($im,"Format png");
		$this->assertEquals(imagesx($im),64,"Width");
		$this->assertEquals(imagesy($im),128,"Height");

		\PHPPE\View::picture("data/test1.png","data/test2.png",1024,1024,false);
		$this->assertFileExists("data/test2.png","Landscape resize");
		@$im = imagecreatefrompng("data/test2.png");
		$this->assertNotNull($im,"Format png");
		$this->assertEquals(imagesx($im),1024,"Width");
		$this->assertEquals(imagesy($im),256,"Height");
		unlink("data/test1.png");
		unlink("data/test2.png");
	
	}
}
?>
