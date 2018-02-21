<?php
/**
 * What You See is What You Get editor Addon
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;

class wyswyg extends \PHPPE\AddOn
{
    public $heightClass="wyswyg";
    public $minHeight=200;
    public $adjust=[24, 500=>48];
    public $conf = "*(iconheight) obj.field";

    function show(  )
    {
        return View::_t(substr($this->name,0,6)=="frame."?@View::getval("frame")[substr($this->name,6)]:$this->value);
    }

    function edit(  )
    {
        View::jslib("wyswyg.js", "pe.wyswyg.init();",8);
        View::css("wyswyg.css");
        if(empty($this->args[0]))
            $this->args[0]=$this->adjust;
        return
        "<textarea id='".$this->fld."' name='".$this->fld."' class='".$this->css." wyswyg' dir='ltr' data-conf='".htmlspecialchars(urlencode(json_encode($this->args)))."'>".
        htmlspecialchars($this->value)."</textarea>";
    }

    public static function validate($n, &$v, $a, $t)
    {
        $v=preg_replace("/<script.*?script>/ims","",$v);
        $v=preg_replace("/(<[^>]+)[\t\r\n\ ]+on[a-z]+[\t\r\n\ ]*=[^>]*/ims","\\1",$v);
        return [1,""];
    }
}

?>
