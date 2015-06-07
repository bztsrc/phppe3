<?php
namespace PHPPE;

class RSS_Ctrl extends App {
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