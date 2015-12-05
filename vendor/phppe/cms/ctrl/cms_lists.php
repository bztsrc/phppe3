<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $lists;
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->needframe = true;
		PHPPE::$core->site = L("CMS Lists");
	}

	function action($item)
	{
		$this->lists = PHPPE::query("list_id as id,list_id as name,parent_id,page_id","pages_list","parent_id=?","","",0,0,[$item]);
		if($item) {
			$this->parentmenu = PHPPE::field("parent_id","pages_list","id=?","","",[$item]);
			if($this->parentmenu) {
				$this->parentparent = PHPPE::field("parent_id","pages_list","id=?","","",[$this->parentmenu]);
			}
		}
	}
}
