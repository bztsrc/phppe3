<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Scripts extends \PHPPE\Ctrl {

	function action_daily($item="")
	{
	}

	function action_hourly($item="")
	{
	}

	function action($item="")
	{
	}

	//! call this frequently if you use file caching
	function action_cachegc($item="") {
		if(!empty(PHPPE::mc()) && method_exists(PHPPE::mc(), "cleanUp") ){
			PHPPE::mc()->cleanUp();
		}
	}
}
?>