<?php
/**
 * @file vendor/phppe/og/libs/OpenGraph.php
 * @author bzt
 * @date 28 Aug 2016
 * @brief
 */

namespace PHPPE;

class og
{
    public $fields = [
    	"fb:app_id"=>"fbAppId",
    	"og:site_name" => "ogSiteName",
    	"og:title" => "name",
    	"og:type" => "ogType",
    	"og:image" => "ogImage",
    	"og:audio" => "ogAudio",
    	"og:video" => "ogVideo",
    	"og:description" => "description"
    ];
	public $cfg;

	public function init($config)
	{
        $this->cfg = $config;
	}

	public function ctrl($app, $method)
	{
	    $appObj = View::getval("app");
        foreach ($this->fields as $fld=>$var) {
	        $appObj->meta[$fld] = [!empty($this->cfg[$var])?$this->cfg[$var]:(!empty($var)&&!empty($appObj->$var)?$appObj->$var:""), "property"];
	    }
		if(!empty($appObj->meta["description"]))
			$appObj->meta["og:description"] = [$appObj->meta["description"], "property"];
		$appObj->meta["og:url"] = [url(), "property"];
	}
}
