<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Views;

// L("layoutadd")
class layoutadd extends \PHPPE\AddOn
{
    public $heightClass = "infobox";

    function edit()
    {
        $quickhelp=!Core::lib("CMS")->expert;
        View::assign("quickhelp",$quickhelp);
        return View::template("cms_layoutadd");
    }

    function save($params)
    {
        $v = new Views();
        $v->id = $params['layoutid'];
        $v->name = $params['layoutname'];
        try {
          if($v->save(true)){
            die("<script>top.document.location.href='".url("cms/layouts/".$params['layoutid'])."';</script>");
          }
        }catch(\Exception $e){
          Core::error($e->getMessage());
        }
    }
}

?>
