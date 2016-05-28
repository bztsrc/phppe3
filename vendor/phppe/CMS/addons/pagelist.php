<?php
/**
 * Addon for page lists
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class pagelist extends \PHPPE\AddOn\setsel
{
    public $heightClass = "setsel_boxw";
    public $headerHeight = 30;
    public $forceFull = 80;

	function init()
	{
		\PHPPE\Core::addon( "pagelist", "CMS Page List Selector", "", "*(views) obj.field options [cssclass]" );
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
		$this->args[1]='lang,tid:template';
		$this->args[2]="<img src='images/lang_%lang%.png' alt='%lang%' style='pointer-events: none;'> %name%";
		$this->args[3]=L("Edit Page List");
		$this->attrs[0]=\PHPPE\Page::getPages(0,$t);
        $this->attrs[1]="setsel_boxw";
		return parent::edit();
	}

    function save($params)
    {
        return \PHPPE\Page::savePageList($this->name, Core::x(",", $params['value']));
    }
}

?>
