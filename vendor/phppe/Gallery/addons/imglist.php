<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class imglist extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_box";
    public $headerHeight = 30;
    public $forceFull = 80;
    public $conf = "*(picturesize,itemheight) obj.field dataset [cssclass]";

    function edit()
    {
        $s=!empty($this->args[0])?intval($this->args[0]):0;
        if (empty(\PHPPE\Gallery::$sizes[$s])) $s=0;
        $h=!empty($this->args[1])?intval($this->args[1]):\PHPPE\Gallery::$sizes[$s][1];
        if ($h>128) $h=128;

        $this->args[0]=0;
        $this->args[1]=0;
        $this->args[2]='';
        $this->args[3]="<img src='gallery/".$s."/%id%' alt='%id%' height='".$h."' style='margin:2px;'>";
        $this->args[4]=L("Edit Image List");
        $this->args[5]="id";
        $this->attrs[0]=\PHPPE\Gallery::getImages();
        $this->attrs[2]="setsel_img";
        $this->attrs[3]=\PHPPE\Gallery::uploadBtn();
        return parent::edit();
    }

    //! to load use DDS: id, img_list, list_id='@ID', , ordering
    function save($params)
    {
        return \PHPPE\Gallery::saveImageList($this->name, str_getcsv($params['value'], ","));
    }
}

?>
