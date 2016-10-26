<?php
namespace PHPPE\AddOn;

class youtube extends \PHPPE\AddOn
{
	public $conf = "([width,[height,[autoplay,[autohide,[controls,[loop,[playlist]]]]]]]) videoid";

	function show()
	{
		if(@$this->args[0]<64) $this->args[0]=380;
		if(@$this->args[1]<64) $this->args[1]=265;
		return "<iframe width='".$this->args[0]."' height='".$this->args[1]."' src='http://www.youtube.com/embed/".htmlspecialchars(!empty($this->value)?$this->value:$this->name)."?".
		(!empty($this->args[2])?"autoplay=1":"").
		(!empty($this->args[3])?"autohide=".intval($this->args[3]):"").
		(!empty($this->args[4])?"controls=".intval($this->args[4]):"").
		(!empty($this->args[5])?"loop=1":"").
		(!empty($this->args[6])?"playlist=".htmlspecialchars($this->args[6]):"").
		"' frameborder='0' allowfullscreen></iframe>";
	}
}

?>