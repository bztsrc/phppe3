<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;
use \PHPPE\Core as Core;

// L("layoutadd")
class layoutadd extends \PHPPE\AddOn
{
    public $heightClass = "infobox";

    function edit()
    {
        $quickhelp=!Core::lib("CMS")->expert;
        \PHPPE\View::assign("quickhelp",$quickhelp);
        return \PHPPE\View::template("cms_layoutadd");
    }

    function save($params)
    {
        $v = new \PHPPE\Views();
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
