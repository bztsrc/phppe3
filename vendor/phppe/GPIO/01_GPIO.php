<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztphp/phppe3/
 *
 *  Copyright LGPL 2015 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/GPIO/01_GPIO.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Interface for Raspberry Pi GPIO
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * Exception class
 */
class GPIOException extends \Exception
{
    public function __construct($message="", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Main class
 */
class GPIO
{
	const PATH_GPIO = '/sys/class/gpio/gpio';
    const PATH_EXPORT = '/sys/class/gpio/export';
    const PATH_UNEXPORT = '/sys/class/gpio/unexport';

	public $pins=[];
	public $hack=[];
	public $hdlr=[];
	static private $self;
/**
 * Register GPIO
 *
 * @param cfg not used
 */
	function init($cfg) {
		PHPPE::lib("GPIO","Raspberry Pi GPIO");
		self::$self=$this;
	}

/**
 * Constructor, loads pin mapping
 *
 * @param cfg not used
 */
	public function __construct($cfg=[])
	{
		$rpi=self::RPi();
		//! get configuration and fallback to hardcoded values
		if(!empty($cfg['pins']))
			$this->pins=PHPPE::str2arr($cfg['pins']);
		if(empty($this->pins)) {
			if ($rpi < 16)
				//! original GPIO without DNC
            	$this->pins = [ 3,5,7,8,10,11,12,13,15,16,18,19,21,22,23,24,26 ];
	        else
    	        //! new GPIO layout (B+)
            	$this->pins = [ 3,5,7,8,10,11,12,13,15,16,18,19,21,22,23,24,26,27,28,29,31,32,33,35,36,37,38,40 ];
		}
	}

	function reset()
	{
		foreach($this->hdlr as $k=>$v) {
			$this->mode($k,"out");
			file_put_contents(self::PATH_UNEXPORT, $k);
		}
		$this->hdlr=[];
	}
 /**
 * Get Raspberry Pi version
 *
 * @return numeric PCB version
 */
    static public function RPi()
    {
        $cpuinfo = @file_get_contents('/proc/cpuinfo');
        if (preg_match('/^Revision[^0-9a-fA-F]+([0-9a-fA-F]+)/', $cpuinfo, $m))
            return hexdec($m[1]);
        return 0;
    }

 /**
 * Get load
 *
 * @return array of three samples (1,5,15 min)
 */
    static public function CPU()
    {
        return sys_getloadavg();
    }

 /**
 * Get CPU temperature
 *
 * @param true for fahrenheit, SI Celsius otherwise
 * @return float
 */
    static public function temp($f = false)
    {
        $t = floatval(@file_get_contents('/sys/class/thermal/thermal_zone0/temp')/1000);
		return ($f?1.8*$t+32:$t);
    }

 /**
 * Get CPU Frequency
 *
 * @return float
 */
    static public function freq()
    {
        return floatval(@file_get_contents('/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq')/1000);
    }

/**
 * Setup pin for direction (in or out)
 *
 * @param pin number
 * @param "in"/"out"
 * @return GPIO instance
 */
    static public function mode($pin, $dir="out")
    {
    	if(!isset(array_keys(self::$self->pins)[$pin])) throw new \GPIOException("bad pin");
    	if($dir!="in"&&$dir!="out") throw new \GPIOException("bad dir");
        // if exported, unexport it first
        if (!empty(self::$self->hdlr[$pin]))
 	       file_put_contents(self::PATH_UNEXPORT,$pin);
        // Export pin
        file_put_contents(self::PATH_EXPORT,$pin);
        file_put_contents(self::PATH_GPIO.$pin.'/direction', $dir);
        self::$self->hdlr[$pin] = $dir;
        return self::$self;
    }

/**
 * Read input value
 *
 * @param  pin
 * @return GPIO value
 */
    static public function read($pin)
    {
    	if(!isset(array_keys(self::$self->pins)[$pin])||@self::$self->hdlr[$pin]!="in") throw new \GPIOException("bad pin");
        return trim(@file_get_contents(self::PATH_GPIO.$pin.'/value'));
    }

/**
 * Write output value
 *
 * @param  pin
 * @param  value
 * @return false|string (GPIO value)
 */
    static public function write($pin,$value=true)
    {
    	if(!isset(array_keys(self::$self->pins)[$pin])||@self::$self->hdlr[$pin]!="out") throw new \GPIOException("bad pin");
        file_put_contents(self::PATH_GPIO.$pin.'/value',$value);
    }

/**
 * String representation of the object.
 */
	function __toString()
	{
		return __CLASS__."/RPi".self::RPi();
	}
}
