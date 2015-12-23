<?php
namespace PHPPE\AddOn;
use \PHPPE\Core as PHPPE;

class notboolean extends \PHPPE\AddOn {
	function init(){\PHPPE\Core::addon("notboolean","!Boolean","","*obj.field");}
	function show() { return !empty($this->value)&&$this->value!=false&&$this->value!=0&&$this->value!="false"?"true":"false"; }
	function edit() { \PHPPE\Core::js("init()","boolean_init();",true);if((!isset($this->value)||($this->value!=true&&$this->value!=1&&$this->value!="true"&&$this->value!="false"&&$this->value!=0&&$this->value!="false"))&&isset($this->args[0])) $this->value=$this->args[0];return "<input class='".$this->css."'".(!empty($this->err)?" ".$this->err:"")." type='checkbox' name='".str_replace(".","_",$this->name)."' value='true' ".($this->value==true||$this->value=="true"||$this->value==1||$this->value=="1"?" checked":"")." data-type='notboolean' data-args='".implode(",",$this->args)."'>";}
}

?>