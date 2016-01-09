<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/core/ctrl/index.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Example action handlers for Self Test Page
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class index extends \PHPPE\Ctrl {
	public $obj;
	public $strings;
	public $_favicon="images/phppe.png";

	function __construct()
	{
		//hardcoded data for demo
		$this->strings=["some","example","string"];

		//example objects. Read from database instead
		$this->obj=new \stdClass();
		$this->obj->field18=array();
		$this->obj->field18[0]=new \stdClass();
		$this->obj->field18[0]->ID=1; $this->obj->field18[0]->Name="product1";$this->obj->field18[0]->Price="123";$this->obj->field18[0]->OnStore="yes";
		$this->obj->field18[1]=new \stdClass();
		$this->obj->field18[1]=array("ID"=>2, "Name"=>"product2", "Price"=>234, "OnStore"=>"yes");
		$this->obj->field18[2]=new \stdClass();
		$this->obj->field18[2]->ID=3; $this->obj->field18[2]->Name="product3";$this->obj->field18[2]->Price="3456";$this->obj->field18[2]->OnStore="no";

		$this->obj->summary=L("Edit me");
	}

	function action($item="")
	{
		if(PHPPE::istry()) {
			//this will retrive the object from dragon's land
			//and validates every field while doing so
			$this->obj=PHPPE::req2obj("obj");
			//I could have used req2arr() as well.

			//you can add additional business class logic here
			//validator only runs for non-empty fields; if you
			//want your field to be validated add an asterix right
			//before field type in template or check presence here.
			if( empty($this->obj->field3) && empty($this->obj->field10) )
				PHPPE::error(L("Field3 should not be empty!"),"obj.field3");

			//finally the updater. This should only be called if everything was ok
			if( !PHPPE::iserror() ) {
				//update database
				//PHPPE::exec("UPDATE obj SET ".PHPPE::obj2str($obj,"ID")." WHERE ID='?'", [$obj->ID] );

				//log what you have done
				//PHPPE::log("I","some info on what's happening");

				//redirect the user so that if he/she reloads the page there will be no problem
				PHPPE::redirect();
			}
		}
	}
}
?>