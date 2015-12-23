<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class imgUpdate extends \PHPPE\AddOn {
	function init()
	{
		\PHPPE\Core::addon("imgUpdate","Image Update","","*(url)");
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