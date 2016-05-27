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

	function init()
	{
		\PHPPE\Core::addon( "imglist", "Image List Selector", "", "*(itemheight) obj.field options [cssclass]" );
	}

	function edit()
	{
		$h=!empty($this->args[0])?intval($this->args[0]):64;

		$this->args[0]=0;//intval($_REQUEST['height'])-24;
		$this->args[1]='';//'lang,tid:template';
		$this->args[2]="<img src='gallery/%id%' alt='%id%' height='".$h."' style='pointer-events: none;margin:2px;' title='%name%'>";
		$this->args[3]=L("Edit Image List");
		$this->attrs[0]=\PHPPE\Gallery::getImages();
        $this->attrs[2]="setsel_img";
		return parent::edit();
	}

    function save($params)
    {
        return \PHPPE\Gallery::saveImageList($this->name, Core::x(",", $params['value']));
    }
}

?>
