<?php
/**
 * Controller for benchmark statistics
 */
namespace PHPPE\Ctrl;

class Benchmark {
    public $data = [];
    public $delta = 0;

	function action()
	{
		$this->data = \PHPPE\Benchmark::stats();
        foreach($this->data as $d)
            if($d['max']-$d['min']>$this->delta)
                $this->delta=sprintf('%.8f',$d['max']-$d['min']);
	}

}
?>
