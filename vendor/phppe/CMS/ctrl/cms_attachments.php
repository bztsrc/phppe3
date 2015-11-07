<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->needframe = false;
		PHPPE::$core->site = L("CMS Attachments");
	}

	function action($item)
	{

	}
}
