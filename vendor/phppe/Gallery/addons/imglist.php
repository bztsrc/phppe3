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
		\PHPPE\Core::addon( "imglist", "Image List Selector", "", "*(templates) obj.field options [cssclass]" );
	}

	function edit()
	{
		$t=!empty($this->args[0])?$this->args[0]:[];
		if(is_string($t)) {
			if($t[0]=='{'||$t[0]=='[') $t=json_decode($t,true);
			else $t=explode(",",$t);
		}
		if(!is_array($t))
			$t=[];

		$this->args[0]=0;//intval($_REQUEST['height'])-24;
		$this->args[1]='';//'lang,tid:template';
		$this->args[2]="<img src='gallery/%id%' alt='%id%' height='64' style='pointer-events: none;margin:2px;'> %name%";
		$this->args[3]=L("Edit Image List");
		$this->attrs[0]=\PHPPE\Gallery::getImages();
		return parent::edit();
	}

    function save($params)
    {
        return \PHPPE\Gallery::saveImageList($this->name, Core::x(",", $params['value']));
    }
}

?>
