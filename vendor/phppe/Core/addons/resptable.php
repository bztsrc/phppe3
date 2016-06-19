<?php
namespace PHPPE\AddOn;
use \PHPPE\View as View;

class resptable extends \PHPPE\AddOn
{
    function init()
    {
        View::jslib("resptable.js","resptable_init();");
    }
}

?>
