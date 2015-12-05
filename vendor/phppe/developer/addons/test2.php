<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class test2 extends \PHPPE\AddOn
{
	function init()
	{
		PHPPE::addon("test2","Test AddOn 2","test1","configuration");
	}
	function show(  )
	{
		return "show2";
	}
	function edit(  )
	{
		return "edit2";
	}
}

?>