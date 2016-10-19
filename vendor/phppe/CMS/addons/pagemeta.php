<?php
/**
 * Addon to edit meta tags of a page
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

// L("pagemeta")
class pagemeta extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 50;

    function load(&$app)
    {
        if (!is_array($this->value)) {
            $page = \PHPPE\View::getval("page");
            if (!empty($page->data['meta']))
                $this->value = $page->data['meta'];
        }
        if (!is_array($this->value))
            $this->value = [];
    }

    function show(  )
    {
        $m=Core::lib("CMS")->metas;
        $v=is_array($this->value)?$this->value:json_decode($this->value,true);
        $r="<table>";
        foreach($m as $k)
            $r.="<tr><td width='1'>".ucfirst(L($k)).":</td><td>".(!empty($v[$k])?$v[$k]:'')."</td></tr>";
        $r.="</table>";
        return $r;
    }

    function edit(  )
    {
        $m=Core::lib("CMS")->metas;
        $v=is_array($this->value)?$this->value:json_decode($this->value, true);
        $r="<div class='infobox' style='padding:5px;overflow:auto;'>";
        foreach($m as $k)
            $r.="<b>".ucfirst(L($k))."</b><br><input class='input form-control' name='".$this->fld."[".$k."]' value='".htmlspecialchars(!empty($v[$k])?$v[$k]:'')."'>";
        $r.="</div>";
        return $r;
    }
}

?>
