<?php
/**
 * Simple captcha form element
 */
namespace PHPPE\AddOn;

use PHPPE\View as View;

class captcha extends \PHPPE\AddOn
{
    public $conf = "*obj.field [cssclass]";

    /**
     * no show function for this add-on
     */
    public function show()
    {
        return "";
    }

    /**
     * generate the puzzle and an input field for it
     */
    public function edit()
    {
        // generate image
        $a=rand(1,16); $b=rand(1,16);
        $_SESSION['captcha']=intval($a+$b);
        $t=$a." + ".$b." =";
        $w=(strlen($t)+2)*9;
        $im=imagecreatetruecolor($w,18);
        for($y=0;$y<18;$y++)
            for($x=0;$x<$w;$x++) {
                $m=$y<10?$y:18-$y;
                imagesetpixel($im,$x,$y,imagecolorallocate($im,rand(0,$m),rand(0,$m),rand(0,$m)));
        }
        $c=imagecolorallocate($im,50,50,50);
        imagestring($im,5,8,0,$t,$c);
        for($i=0;$i<3;$i++) imageline($im,rand(0,$w-16),0,rand(0,$w-16),18,$c);
        ob_start();
        imagepng($im);
        $d=ob_get_contents();
        ob_end_clean();
        imagedestroy($im);
        // output html with image and an input field
        return "<div><img src='data:image/png;base64,".base64_encode($d)."' style='vertical-align:middle;'>".
            "<input".@View::v($this, $this->attrs[0])." size='2' style='width:70px;display:inline-block;' ".
            "type='number' onfocus='this.className=this.className.replace(\" errinput\",\"\");' value=\"\"></div>";
    }

    /**
     * validate captcha value
     */
    public static function validate($n, &$v, $a, $t)
    {
        $c=$_SESSION['captcha']; unset($_SESSION['captcha']);
        return [ $v==$c, 'is a required field.' ];
    }
}
