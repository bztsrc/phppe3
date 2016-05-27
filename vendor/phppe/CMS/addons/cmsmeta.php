<?php
/**
 * Addon to edit meta tags of a page
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

class cmsmeta extends \PHPPE\AddOn
{
	function show(  )
	{
		$m=Core::lib("CMS")->metas;
		$v=is_array($this->value)?$this->value:json_decode($this->value,true);
		$r="<table>";
		foreach($m as $k)
			$r.="<tr><td width='1'>".ucfirst(L($k)).":</td><td>".(!empty($v[$k])?$v[$k]:'')."</td></tr>";
		$r.="</table>";
		return $r;
	}

	function edit(  )
	{
		$m=Core::lib("CMS")->metas;
		$v=is_array($this->value)?$this->value:json_decode($this->value, true);
        $r="";
		foreach($m as $k)
			$r.="<div style='width:20%;'><span><i>".ucfirst(L($k))."</i></span></div><div style='width:80%;'><input class='input form-control' name='".$this->fld."[".$k."]' value='".htmlspecialchars(!empty($v[$k])?$v[$k]:'')."'></div>";
		return $r;
	}
}

?>
