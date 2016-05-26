<?php

//for ORM test
class TestModel extends \PHPPE\Model {
	public $id;
	public $name;
	public $parentId;
	protected static $_table="test";

	function __construct($id=0) {
		$this->parentId=0;
		parent::__construct($id);
	}
}

class BadModel extends \PHPPE\Model {
	public $id;
	public $name;
	public $parentId;
	protected static $_table="";
}
