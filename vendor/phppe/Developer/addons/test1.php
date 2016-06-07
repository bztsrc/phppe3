<?php
/**
 * Testing addons
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class test1 extends \PHPPE\AddOn
{
    function init()
    {
    }
	function show(  )
	{
		return "show1";
	}
	function edit(  )
	{
		return "edit1";
	}
}

?>