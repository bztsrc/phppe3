<?php
namespace PHPPE;

class Scripts extends App {

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