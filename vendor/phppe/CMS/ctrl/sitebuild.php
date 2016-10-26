<?php
/**
 * @file vendor/phppe/CMS/ctrl/sitebuild.php
 * @author bzt
 * @date 26 May 2016
 * @brief sitebuild importer
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSSitebuild
{
    static $success=false;
/**
 * default action, loaded via AJAX
 */
    function action($item)
    {
        //! assets
        if (!empty($_REQUEST['assetn'])) {
            list($d,$f)=explode("/",$_REQUEST['assetn']);
            $fn=".tmp/".session_id()."/".$d."/".$f;
            if(file_exists($fn)) {
                header("Content-type: ".(
                    $d=="i"?"image/png":(
                    $d=="c"?"text/css":(
                    $d=="j"?"text/javascript":
                    "application/octet-stream"
                    ))));
                die(file_get_contents($fn)."");
            }
        }

		Core::$core->noframe=true;
        $import=Core::req2arr("import");

        //! upload
        if (!empty($import['file']['tmp_name'])) {
            @\PHPPE\Tools::rmdir(".tmp/".session_id());
            @mkdir(".tmp/".session_id()."/i",0750,true);
            @mkdir(".tmp/".session_id()."/c",0750,true);
            @mkdir(".tmp/".session_id()."/j",0750,true);
            @mkdir(".tmp/".session_id()."/f",0750,true);
            @mkdir(".tmp/".session_id()."/h",0750,true);
            \PHPPE\Tools::untar($import['file']['tmp_name'], function($name, $body){
                $fn="";
                if(substr($name,-4)==".htm"||substr($name,-5)==".html") {
                    self::$success=true;
                    $fn="h/".basename($name);
                } else
                if(in_array(substr($name,-4),[".gif",".png",".pnm",".jpg",".svg"])) {
                    $fn="i/".basename($name);
                } else
                if(in_array(substr($name,-4),[".eot",".ttf"])||substr($name,-5)==".woff"||substr($name,-6)==".woff2") {
                    $fn="f/".basename($name);
                } else
                if(substr($name,-4)==".css") {
                    $fn="c/".basename($name);
                } else
                if(substr($name,-3)==".js") {
                    $fn="j/".basename($name);
                }
                if(!empty($fn)) {
                    file_put_contents(".tmp/".session_id()."/".$fn, $body);
                }
            });
            if(!self::$success) {
                Core::error("Bad archive");
                return;
            }
        }

        //! choose a html
        $this->htmls=glob(".tmp/".session_id()."/h/*");
        if(count($this->htmls)==1) $item=1;
        if(intval($item)>0 && !empty($this->htmls[$item-1])) {
            $html=$this->htmls[$item-1];
            unset($this->htmls);
        }
        if(empty($html)) {
            if(intval($item)>0)
                Core::error("Bad archive");
            return;
        }

        //! choose application area
        $data=preg_replace("/<script.*?\/script>/ims","",file_get_contents($html));
        $files=glob(".tmp/".session_id()."/*/*");
        $assets=["i"=>"images","c"=>"css","j"=>"js","f"=>"fonts"];
        foreach ($files as $f) {
            if (!empty($assets[basename(dirname($f))])) {
                $data=preg_replace(
                    "/[^=\ \t\r\n\'\",\(\[]+".basename($f)."/ims",
                    url("cms/sitebuild")."?assetn=".basename(dirname($f))."/".basename($f),
                    $data);
            }
            if (basename(dirname($f))=="c") {
                View::css(url("cms/sitebuild")."?assetn=".basename(dirname($f))."/".basename($f));
            } else
            if (basename(dirname($f))=="j") {
                View::jslib(url("cms/sitebuild")."?assetn=".basename(dirname($f))."/".basename($f));
            }
        }
        $this->content=\PHPPE\CMS::taghtml($data);
        if (empty($_REQUEST['chooseid']) &&
            preg_match("/(<[^<>]*?id=[\'\"]?content[^>]*?>)/ims",$this->content,$m) &&
            !empty($m[0]) && preg_match("/data\-chooseid=[\'\"]?([0-9]+)/ims",$m[0],$M)){
            $_REQUEST['chooseid']=$M[1];
        }
        if (!empty($_REQUEST['chooseid'])) {
            $t=\PHPPE\CMS::splithtml($this->content,$_REQUEST['chooseid'],0).
            "<!app>".\PHPPE\CMS::splithtml($this->content,$_REQUEST['chooseid'],2);
            preg_match_all("/[^=\ \t\r\n\'\",\(\[]+\?assetn=([a-z])\/([^=\ \t\r\n\'\",\)\]]+)/ims",
                $t,$m,PREG_SET_ORDER);
            foreach($m as $M) {
                $t=str_replace($M[0],$assets[$M[1]]."/".$M[2],$t);
            }
            $name=strtr(basename($html),[".html"=>"",".htm"=>""]);
            if($name=="index"||$name=="frame"||$name=="simple"||$name=="default")
                $name="sitebuild".Core::$core->now;
            $views = \PHPPE\Views::find($name);
            if(!empty($views))
                $name.=Core::$core->now;
            $view = new \PHPPE\Views();
            $view->id = $name;
            $view->name = $name;
            $view->sitebuild = $name;
            $view->data = preg_replace("/<!\-\-.*?\-\->/ms","",$t);
            $view->created = date("Y-m-d H:i:s", Core::$core->now);
            foreach ($files as $f) {
                if (empty($assets[basename(dirname($f))])) continue;
                if (basename(dirname($f))=="c") $view->css[]=basename($f);
                if (basename(dirname($f))=="j") $view->jslib[]=basename($f);
            }
            if ($view->save(true)) {
                foreach($assets as $k=>$v) {
                    chdir(".tmp/".session_id()."/".$k);
                    \PHPPE\Tools::copy(glob("*"),"public/".$v);
                    chdir("../../..");
                }
                @\PHPPE\Tools::rmdir(".tmp/".session_id());
                Http::redirect("cms/layouts/".$name);
            }
            Core::error("Unable to save sitebuild!");
        }
    }

}
