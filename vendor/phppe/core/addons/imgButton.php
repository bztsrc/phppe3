<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class imgButton extends \PHPPE\AddOn {
	function init()
	{
		\PHPPE\Core::addon("imgButton","Image Button");
	}
	function show()
	{
		return htmlspecialchars($this->value);
	}
	function edit()
	{
		return "";
	}
}

?>