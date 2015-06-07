<?php
use PHPPE\Core as PHPPE;

class CMS_Ctrl extends \PHPPE\App {
	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->site = L("CMS Attachments");
	}

	function action($item)
	{

	}
}
