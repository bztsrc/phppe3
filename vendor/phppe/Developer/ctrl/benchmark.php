<?php
/**
 * Controller for benchmark statistics
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;
use PHPPE\Benchmark;

class BenchmarkController {
    public $data = [];
    public $urls = [];
    
    function __construct()
    {
        Core::$core->nocache = true;
    }

	function action()
	{
        if(isset($_REQUEST['clearbenchmark'])) {
            Benchmark::clear();
            Http::redirect();
        }
        $this->data = Benchmark::stats();
        foreach($this->data as $u=>$v)
            $this->urls[] = $u." (".sprintf(L("%d samples"), reset($v)['cnt']).")";

        View::js("choosediv(value)", "var divs=document.querySelectorAll('DIV.benchmark');for(var i in divs) divs[i].style.display=divs[i].id=='url'+value?'block':'none';");
    }

}
?>
