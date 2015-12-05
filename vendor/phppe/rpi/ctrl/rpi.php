<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class RPi extends \PHPPE\Ctrl {
	public $pins;
	public $values;

	function __construct()
	{
		PHPPE::lib("GPIO")  ->mode(2,"out")
				    ->mode(3,"out")
				    ->mode(4,"out")
				    ->mode(18,"out")
				    ;
	}

	function action_setgpio($item)
	{
		PHPPE::lib("GPIO")->write($item,true);
		die("SET OK");
	}

	function action_clrgpio($item)
	{
		PHPPE::lib("GPIO")->write($item,false);
		die("CLR OK");
	}

	function action($item)
	{
		//get current status
		foreach(PHPPE::lib("GPIO")->hdlr as $k=>$v)
			$this->values[PHPPE::lib("GPIO")->pins[$k]]=PHPPE::lib("GPIO")->read($k);

		//expand pin list with VCC, GND pins
		$this->pins = array_flip(PHPPE::lib("GPIO")->pins) +
		 [1=>"3.3V",2=>"5V",3=>2,4=>"5V",5=>3,6=>"GND",8=>14,9=>"GND",10=>15,14=>"GND",17=>"3.3V",20=>"GND",25=>"GND"];
		 ksort($this->pins);
		PHPPE::lib("GPIO")->hdlr += ["3.3V"=>"out","5V"=>"out","GND"=>"in"];

		//add css and js
		PHPPE::css("rpi.css");
		PHPPE::js("rpi_swgpio(obj,id)","var cmd='',v=Math.floor(obj.getAttribute('data-value'));if(v==1) { obj.setAttribute('data-value','0'); obj.style.background='#006000'; cmd='clr'; } else { obj.setAttribute('data-value','1'); obj.style.background='#0F0'; cmd='set'; }var http_request = new XMLHttpRequest();http_request.open('GET','".url()."'+cmd+'gpio/'+id,false);if(cmd)http_request.send();");
	}
}
