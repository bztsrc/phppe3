<?php
/**
 * Addon to edit meta tags of a page
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class cmsmeta extends \PHPPE\AddOn
{
	function init()
	{
		PHPPE::addon("cmsmeta", "CMS Meta info", "", "*");
	}

	function show(  )
	{
		$m=PHPPE::lib("CMS")->metas;
		$v=is_array($this->value)?$this->value:json_decode($this->value,true);
		$r="<table>";
		foreach($m as $k)
			$r.="<tr><td width='1'>".ucfirst(L($k)).":</td><td>".(!empty($v[$k])?$v[$k]:'')."</td></tr>";
		$r.="</table>";
		return $r;
	}

	function edit(  )
	{
		$m=PHPPE::lib("CMS")->metas;
		$v=is_array($this->value)?$this->value:json_decode($this->value,true);
		$r="<table width='100%'>";
		foreach($m as $k)
			$r.="<tr><td width='1'>".ucfirst(L($k))."</td><td width='*'><input class='input' name='".$this->fld.":".$k."' value='".htmlspecialchars(!empty($v[$k])?$v[$k]:'')."'></td></tr>";
		$r.="</table>";
		return $r;
	}

	static function validate($n, &$v, $a=[], $t=[])
	{
		$v=[];
		foreach($_REQUEST as $k=>$val) {
			$d=explode(":",$k);
			if($d[0]==$n && !empty($d[1]) && !empty($val))
				$v[$d[1]]=trim($val);
		}
		if(empty($v)) $v="";
		return[ true, "" ];
	}
}

?>