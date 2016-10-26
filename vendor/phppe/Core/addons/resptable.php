<?php
namespace PHPPE\AddOn;

use PHPPE\View;

class resptable extends \PHPPE\AddOn
{
    function init()
    {
        View::jslib("resptable.js","pe.resptable.init();");
    }
}

?>
