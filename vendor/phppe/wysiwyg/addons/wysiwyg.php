<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class wysiwyg extends \PHPPE\AddOn
{
	function init()
	{
		PHPPE::addon("wysiwyg", "WYSIWYG Editor", "", "");
	}
	function show(  )
	{
		return PHPPE::_t($this->value);
	}
	function edit(  )
	{
		PHPPE::jslib("wysiwyg.js", "wysiwyg_init();");
		return "<div id='".htmlspecialchars(str_replace(".","_",$this->name)).":container' class='wysiwyg' data-conf='".htmlspecialchars(urlencode(json_encode($this->args)))."'>".
		"<textarea id='".htmlspecialchars(str_replace(".","_",$this->name))."' name='".htmlspecialchars(str_replace(".","_",$this->name))."' class='wysiwyg wysiwyg_edit input'>".htmlspecialchars($this->value)."</textarea></div>";
	}
}

?>