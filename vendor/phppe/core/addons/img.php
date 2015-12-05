<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class img extends \PHPPE\AddOn {
	function init()
	{
		\PHPPE\Core::addon("img","Image");
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