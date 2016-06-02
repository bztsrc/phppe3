<?php
/**
 * Controller for benchmark statistics
 */
namespace PHPPE\Ctrl;

class Benchmark {
    public $data = [];
    public $urls = [];
    
    function __construct()
    {
        \PHPPE\Core::$core->nocache = true;
    }

	function action()
	{
        if(isset($_REQUEST['clearbenchmark'])) {
            \PHPPE\Benchmark::clear();
            \PHPPE\Http::redirect();
        }
        $this->data = \PHPPE\Benchmark::stats();
        foreach($this->data as $u=>$v)
            $this->urls[] = $u." (".sprintf(L("%d samples"), reset($v)['cnt']).")";

        \PHPPE\View::js("choosediv(value)", "var divs=document.querySelectorAll('DIV.benchmark');for(var i in divs) divs[i].style.display=divs[i].id=='url'+value?'block':'none';");
    }

}
?>
