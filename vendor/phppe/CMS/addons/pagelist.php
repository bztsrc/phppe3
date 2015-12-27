<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

include_once("vendor/phppe/core/addons/setsel.php");
include_once("vendor/phppe/CMS/libs/views.php");
include_once("vendor/phppe/CMS/libs/pages.php");

class pagelist extends \PHPPE\AddOn\setsel
{
	function init()
	{
		\PHPPE\Core::addon( "pagelist", "CMS Page List Selector", "", "*(templates) obj.field options [skipids [onchangejs [cssclass]]]" );
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

		$this->args[0]=intval($_REQUEST['h'])-24;
		$this->args[1]='lang,tid:template';
		$this->args[2]="<img src='images/lang_%lang%.png' alt='%lang%'> %name%";
		$this->args[3]=L("Edit Page List");
		$this->attrs[0]=\Page::getPages();
		return parent::edit();
	}
}

?>