<?php
/**
 * @file vendor/phppe/og/libs/OpenGraph.php
 * @author bzt
 * @date 28 Aug 2016
 * @brief
 */

namespace PHPPE;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Registry as Registry;

class og
{
    public $fields;

	public function init($config)
	{
        $this->fields = $config;
	}

	public function ctrl($app, $method)
	{
	    $appObj = View::getval("app");
        foreach ($this->fields as $fld=>$var) {
            if (empty($var)) $var = $fld;
	        $appObj->meta[$fld] = [!empty($appObj->$var)?$appObj->$var:Registry::get($fld), "property"];
	    }
		if(!empty($appObj->meta["description"]))
			$appObj->meta["og:description"] = [$appObj->meta["description"], "property"];
		$appObj->meta["og:url"] = [url(), "property"];
	}
}
