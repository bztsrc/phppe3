<?php
/**
 * What You See is What You Get editor Addon
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;
use \PHPPE\View as View;

class wyswyg extends \PHPPE\AddOn
{
    public $heightClass="wyswyg";
    public $adjust=[24, 500=>48];

    function init()
    {
        Core::addon("wyswyg", "WYSiWYG", "", "*(iconheight) obj.field");
    }

    function show(  )
    {
        return View::_t(substr($this->name,0,6)=="frame."?@View::getval("frame")[substr($this->name,6)]:$this->value);
    }

    function edit(  )
    {
        View::jslib("wyswyg.js", "wyswyg_init();");
        View::css("wyswyg.css");
        if(empty($this->args[0]))
            $this->args[0]=$this->adjust;
        return
        "<textarea id='".$this->fld."' name='".$this->fld."' class='".$this->css." wyswyg' dir='ltr' data-conf='".htmlspecialchars(urlencode(json_encode($this->args)))."'>".
        htmlspecialchars($this->value)."</textarea>";
    }
}

?>
