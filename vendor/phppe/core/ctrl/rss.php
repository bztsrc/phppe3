<?php
namespace PHPPE\Ctrl;

class RSS extends \PHPPE\Ctrl {
	public $mimetype="text/xml";
	public $ttl=100;
	public $results=[];

	function __construct($cfg){
		\PHPPE\Core::$core->needframe=false;
		\PHPPE\Core::$core->output="rss";

		$this->results=[
			["title"=>"title","description"=>"description","category"=>"category","link"=>url("link")]
		];
	}

}
?>