<?php
/**
 * @file vendor/phppe/DBA/ctrl/dba.php
 * @author bzt
 * @date 29 Sep 2016
 * @brief
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\View;

include("vendor/phppe/DBA/libs/crop.php");

class DBA
{
/**
 * Action handler
 */
    function action($item)
    {
        if(!Core::$user->has('siteadm')) {
            Core::$core->template="403";
        } else {
            $this->tables = \PHPPE\DBA::tables();
            if(Core::$core->action=="action") {
                Http::redirect(Core::$core->app."/".reset($this->tables));
            }
            $this->columns = \PHPPE\DBA::columns(Core::$core->action);
            Core::$core->noframe = true;
    
            $this->dba = new \stdClass;
            $this->dba->table = Core::$core->action;
            $this->dba->search = trim(!empty($_REQUEST['q'])?$_REQUEST['q']:"");
            
            if(!empty($this->dba->search)) {
                $srch="";
                foreach($this->columns as $c) {
                    $srch .= ($srch?"||','||":"").$c['id'];
                }
                $this->rows = \PHPPE\DS::query(
                    "*",
                    Core::$core->action,
                    $srch." like ?",
                    "","",0,8192,
                    [\PHPPE\DS::like($this->dba->search)]
                );
            } else {
                $this->rows = \PHPPE\DS::query("*",Core::$core->action,"","","",0,8192);
            }
    
    
            View::css("dba.css");
            View::js('switchtable(val)','document.location.href="'.Core::$core->app.'/"+val;');
            View::js('searchtable(val)','document.location.href="'.Core::$core->app."/".Core::$core->action.'?q="+urlencode(val);');
            View::js('init()','pe_ot=70;',true);
        }
    }
}
