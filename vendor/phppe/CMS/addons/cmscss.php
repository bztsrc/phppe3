<?php
/**
 * Addon to select stylesheets for a layout
 */
namespace PHPPE\AddOn;

class cmscss extends \PHPPE\AddOn\text
{
	public $conf="*";

	function show(  )
	{
		$v=$this->toArr($this->value);
		return implode("<br/>",$v);
	}

	function edit(  )
	{
		$this->value=implode("\n",$this->toArr($this->value));
		$this->args=[32768,3];
		return parent::edit();
	}

	function toArr($v) {
		if(is_array($v))
			return $v;
		if(@$v[0]=='{'||@$v[0]=='[')
			return json_decode($v,true);
		return explode("\n",$v);
	}

	static function validate($n, &$v, $a=[], $t=[])
	{
		if(!is_array($v))
			$v=explode("\n",str_replace("\r","",$v));
		if(empty($v[0]) || empty($v)) $v="";
		return[ true, "" ];
	}
}

?>