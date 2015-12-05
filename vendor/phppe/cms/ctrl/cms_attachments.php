<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->needframe = true;
		PHPPE::$core->site = L("CMS Attachments");
	}

	function action($item)
	{

	}
}
