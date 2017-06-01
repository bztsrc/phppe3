<?php
/**
 * Controller for RPi web interface
 */
namespace PHPPE\Ctrl;
use PHPPE\Core;
use PHPPE\View;

class RPi {
    public $pins;

    /* ajax hook */
    function status($item)
    {
        foreach(Core::lib("GPIO")->pins as $idx=>$pin) {
            echo("pin".$pin."\t".
                "pin_".@Core::lib("GPIO")->hdlr[$idx]." ".
                "pin_".(Core::lib("GPIO")->get($idx)?"on":"off")."\n"
            );
        }
        die();
    }

    /* ajax hook */
    function toggle($item) {
        $item = intval(substr($item,3));
        try{
            Core::lib("GPIO")->set($item, Core::lib("GPIO")->get($item) ? false : true);
            die("OK");
        }catch(\Exception $e) {
            die("ERR ".$e->getMessage());
        }
    }

    function action($item)
    {
        if(!empty(Core::lib("GPIO"))) {
            //expand pin list with VCC, GND pins
            $this->pins = array_flip(Core::lib("GPIO")->pins) +
             [1=>"3.3V",2=>"5V",3=>2,4=>"5V",5=>3,6=>"GND",8=>14,9=>"GND",10=>15,14=>"GND",17=>"3.3V",20=>"GND",25=>"GND"];
             ksort($this->pins);
            Core::lib("GPIO")->hdlr += ["3.3V"=>"out","5V"=>"out","GND"=>"in"];
            foreach($this->pins as $pin=>$type) {
                $this->smenu[] = [
                    "list_id" => "gpio",
                    "id" => $pin,
                    "name" => $type,
                    "posx" => (($pin-1)%2)*100+20,
                    "posy" => floor(($pin-1)/2)*40+100,
                    "type" => $type=="GND"?"pin_gnd":(substr($type,-1)=="V"?"pin_volt":
                        "pin_".@Core::lib("GPIO")->hdlr[$type]." pin_off")
                ];
            }
            //add css and js
            View::css("rpi.css");
            View::jslib("smenu.js",'pe.smenu.init({"link":"rpi_toggle(\"@ID\");", "url":"gpio", "callback":"/rpi/status", "refresh":"2000"});');
            View::js("rpi_toggle(id)", "var r = new XMLHttpRequest();r.open('GET', '/rpi/toggle/'+id, true);r.send(null);");
        } else {
            Core::error("Please run it on a Raspberry Pi");
        }
    }
}
