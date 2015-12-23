<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class youtube extends \PHPPE\AddOn
{
	function init()
	{
		PHPPE::addon("youtube", "YouTube video", "", "([width,[height]]) videoid");
	}
	function show(  )
	{
		return htmlspecialchars($this->value);
	}
	function edit(  )
	{
		return htmlspecialchars($this->value);
	}
}

?>