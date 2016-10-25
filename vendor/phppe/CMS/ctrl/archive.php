<?php
/**
 * @file vendor/phppe/CMS/ctrl/archive.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSArchive
{
    public $title;
    public $result;
/**
 * default action
 */
    function action($item)
    {
        if(!Core::$user->has("siteadm|webadm"))
            Http::redirect("403");
        if(isset($_REQUEST['pagedel'])) {
            if(!empty($_REQUEST['created']))
                \PHPPE\Page::delete($item,$_REQUEST['created']);
            Http::redirect($item);
        }
        if(isset($_REQUEST['revert'])) {
            if(!empty($_REQUEST['created'])) {
                \PHPPE\Core::log('A',sprintf("Page %s reverted to %s by %s",$item,$_REQUEST['created'],\PHPPE\Core::$user->name), "cmsaudit");
                \PHPPE\DS::exec("UPDATE pages set created=CURRENT_TIMESTAMP,modifyd=CURRENT_TIMESTAMP,modifyid=? WHERE id=? AND (lang='' OR lang=?) AND created=?",
                    [Core::$user->id,$item,Core::$client->lang,$_REQUEST['created']]);
            }
            Http::redirect($item);
        }
        if(empty($_REQUEST['created'])) {
            $_REQUEST['created'] = \PHPPE\DS::field( "created", "pages", "(id=? OR ? LIKE id||'/%') AND (lang='' OR lang=?) AND publishid!=0", "", "id DESC,created DESC",[$item,$item,Core::$client->lang]);
            if(empty($_REQUEST['created'])) {
                $_REQUEST['created'] = \PHPPE\DS::field( "created", "pages", "(id=? OR ? LIKE id||'/%') AND (lang='' OR lang=?)", "", "id DESC,created ASC",[$item,$item,Core::$client->lang]);
            }
        }

        $frame = \PHPPE\DS::fetch("data,dds","pages","id='frame' AND (lang='' OR lang=?) AND created<=?","","",[Core::$client->lang,$_REQUEST['created']]);
        $frame['dss'] = @json_decode($frame['dds'],true);
        $frame['data'] = @json_decode($frame['data'],true);
        \PHPPE\View::assign("frame",$frame['data']);

        //! load archive version
        $page = \PHPPE\DS::fetch( "*", "pages", "(id=? OR ? LIKE id||'/%') AND (lang='' OR lang=?) AND created=?", "", "id DESC,created DESC",[$item,$item,Core::$client->lang,$_REQUEST['created']]);
        $this->title = $page['name'];
        $title = L("ARCHIVE")." ".$page['name'];
        if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
        if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;}
        foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$page[$k];
        $p=@array_merge($frame['dds'],@json_decode($page['dds'],true));
        if(is_array($p)) {
                foreach($p as $k => $c)
                        if($k != "dds") {
                                try{
                                $this->$k = \PHPPE\DS::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], \PHPPE\View::getval(@ $c[ 6 ]));
                                } catch(\Exception $e) {Core::log("E",$item." ".$e->getMessage()." ".implode(" ",$c),"dds");}
                        }
        }
        $old = \PHPPE\View::template($this->template);

        if(isset($_REQUEST['diff'])) {
                include_once("vendor/phppe/CMS/libs/simplediff.php");

                //! load current version
                $frame = \PHPPE\DS::fetch("data,dds","pages","id='frame' AND (lang='' OR lang=?) AND publishid!=0","","created DESC",[Core::$client->lang]);
                $frame['dss'] = @json_decode($frame['dds'],true);
                $frame['data'] = @json_decode($frame['data'],true);
                \PHPPE\View::assign("frame",$frame['data']);

                $page = \PHPPE\DS::fetch( "*", "pages", "id=? OR ? LIKE id||'/%'", "", "id DESC,created DESC",[$item,$item]);
                if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
                if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;}
                foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$page[$k];
                $p=@array_merge($frame['dds'],@json_decode($page['dds'],true));
                if(is_array($p)) {
                        foreach($p as $k => $c)
                                if($k != "dds") {
                                        try{
                                        $this->$k = \PHPPE\DS::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], \PHPPE\View::getval(@ $c[ 6 ]));
                                        } catch(\Exception $e) {Core::log("E",$item." ".$e->getMessage()." ".implode(" ",$c),"dds");}
                                }
                }
                $this->title = $page['name'];
                $curr = \PHPPE\View::template($this->template);
                //! make sure diff splits on tag end
                $this->result=htmlDiff(preg_replace("/>([^\ \t\n])/m","> \\1",$old),preg_replace("/>([^\ \t\n])/m","> \\1",$curr));
                //! remove diff inside tags
                $this->result=preg_replace("/(<[^<>]+)<ins>.*?<\/ins>([^<>]*>)/ims","\\1\\2",$this->result);
                $this->result=preg_replace("/(<[^<>]+)<del>(.*?)<\/del>([^<>]*>)/ims","\\1\\2\\3",$this->result);
        } else
                $this->result=$old;
        $this->title=$title;
    }
}
