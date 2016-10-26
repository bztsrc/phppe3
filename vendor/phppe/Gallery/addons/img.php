<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Gallery;

class img extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_box";
    public $headerHeight = 30;
    public $forceFull = 80;
    public $conf = "*(picturesize,itemheight) obj.field dataset [cssclass]";

    function edit()
    {
        $s=!empty($this->args[0])?intval($this->args[0]):0;
        if (empty(Gallery::$sizes[$s])) $s=0;
        $h=!empty($this->args[1])?intval($this->args[1]):Gallery::$sizes[$s][1];
        if ($h>128) $h=128;

        $this->args[0]=1;
        $this->args[1]=0;
        $this->args[2]='';
        $this->args[3]="<img src='gallery/".$s."/%id%' alt='%id%' height='".$h."' style='margin:2px;'>";
        $this->args[4]=L("Select Image");
        $this->args[5]="id";
        $this->attrs[0]=Gallery::getImages();
        $this->attrs[2]="setsel_img";
        $this->attrs[3]=Gallery::uploadBtn();
        return parent::edit();
    }

    function save($params)
    {
        $page=View::getval("page");
        $page->setParameter($this->name, str_getcsv($params['value'],",")[0]);
        return $page->save();
    }
}

?>
