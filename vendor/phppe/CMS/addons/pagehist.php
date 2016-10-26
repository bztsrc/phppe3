<?php
/**
 * Addon for page meta information
 */
namespace PHPPE\AddOn;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\DS;

// L("pagehist")
class pagehist extends \PHPPE\AddOn
{
    public $heightClass = "infobox";
    public $forceFull = 60;

    function load(&$app)
    {
        $app->versions = DS::query(
            "a.*,b.name as moduser,c.name as pubuser, CURRENT_TIMESTAMP as ct",
            "pages a left join users b on a.modifyid=b.id left join users c on a.publishid=c.id",
            "a.id=? AND (a.lang='' OR a.lang=?)",
            "", "a.created DESC", 0, 0, [$_SESSION['cms_url'], Core::$client->lang]);
    }

    function edit()
    {
        return View::template("cms_pagehist");
    }

}

?>
