<?php

//for ORM test
class TestModel extends \PHPPE\Model {
	public $id;
	public $name;
	public $parentId;
	protected static $_table="test";

	function __construct($name) {
		$this->name=$name;
		$this->parentId=0;
	}
}

class BadModel extends \PHPPE\Model {
	public $id;
	public $name;
	public $parentId;
	protected static $_table="";
}
