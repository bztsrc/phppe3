<?php
/**
 * Page model
 */
namespace PHPPE;

class Page extends \PHPPE\Model
{
    public $lang = "";
    public $template = "";
    public $data = [];
    public $dds = [];
    public $created = "";
    public $modifyd = "";
    public $modifyid = "";
    public $ownerid = "";
    public $lockd = 0;
    public $pubd = 0;
    public $expd = 0;

    static $_table = "pages";
    static $_history = false;

/**
 * Model constructor
 *
 * @param id optional
 */
    function __construct($id="",$created="")
    {
        if(!empty($id)) {
            if(empty($created))
                $ret=$this->load($id,"","created DESC");
            else
                $ret=$this->load([$id,$created],"id=? AND created=?");
            if($ret)
                $this->id = $id;
        }
        static::$_history = !empty(Core::lib("CMS")->revert);
    }

/**
 * Locks a page. Called when a page parameter edited.
 * Save will release it automatically.
 *
 * @return bool success
 */
    function lock()
    {
        //! check input
        if(empty($this->id))
            return true;
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        //! release old locks (locked for more than 10 minutes)
        DS::exec("UPDATE ".static::$_table." SET ownerid=0 WHERE lockd<?",
            [ date("Y-m-d H:i:s", Core::$core->now-600) ]);
        //! try to lock with one atomic query
        DS::exec("UPDATE ".static::$_table.
            " SET ownerid=?,lockd=CURRENT_TIMESTAMP WHERE id=? AND lang=? AND ownerid=0",
            [Core::$user->id, $this->id, $this->lang]);
        //! get the new owner
        $this->ownerid = DS::field(
            "ownerid", "pages", "id=? AND lang=?", "", "created DESC",
            [$this->id, $this->lang]);
        //! success if it's the current user
        return $this->ownerid == Core::$user->id;
    }

/**
 * Release a page lock. Called when a page parameter saved.
 *
 * @return bool success
 */
    function release()
    {
        //! check input
        if(empty($this->id))
            throw new \Exception(L('No page id'));
        //! release
        $this->ownerid = 0;
        return DS::exec("UPDATE ".static::$_table.
            " SET ownerid=0,lockd=0 WHERE id=? AND lang=?",
            [ $this->id, $this->lang]) > 0;
    }

/**
 * Unlock all pages that are locked by a given user
 *
 * @param userid
 *
 * @return number of pages released
 */
    static function unLock($userid)
    {
        if(empty($userid))
            return 0;
        return DS::exec("UPDATE ".static::$_table." SET ownerid=0 WHERE ownerid=?",
            [ $userid ]);
    }

/**
 * Save a page
 *
 * @param force use of insert instead of update
 *
 * @return boolean success
 */
    function save($force=false)
    {
        //! check input
        if (!empty($this->id) && $this->id[0]=="/")
            $this->id=substr($this->id, 1);
        $this->id=strtr($this->id,["\'"=>"","\""=>"","#"=>"","?"=>""]);
        if (empty($this->id))
            throw new \Exception(L('No page id'));
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        $d = DS::db();
        if (empty($d))
            throw new \Exception('no ds');
        //! set up properties
        $this->ownerid = 0;
        $this->lockd = "";
        $this->modifyd = date("Y-m-d H:i:s", Core::$core->now);
        $this->modifyid = Core::$user->id;
        if (static::$_history || $force)
            $this->created = $this->modifyd;
        //! build the arguments array
        $a = [];
        foreach ($this as $k => $v) {
            if ($k[0] != '_' && $k!="created") {
                $a[$k] = is_scalar($v) ? $v : json_encode($v);
            }
        }
        $a['publishid'] = static::$_history?0:Core::$user->id;
        //! write audit log
        Core::log('A',sprintf("Page %s saved by %s",$this->id,Core::$user->name), "cmsaudit");
        //! save page
        if (!DS::exec((!static::$_history && !$force ?
            'UPDATE '.static::$_table.' SET '.implode('=?,', array_keys($a)).
                '=? WHERE id='.$d->quote($this->id).' AND lang='.$d->quote($this->lang) :
            'INSERT INTO '.static::$_table.' ('.implode(',', array_keys($a)).') VALUES (?'.str_repeat(',?', count($a) - 1).')'),
            array_values($a)))
            return false;
        //! purge old records
        DS::exec("DELETE FROM ".static::$_table." WHERE id=? AND lang=? AND publishid!=0 AND created not in (SELECT created FROM ".static::$_table." WHERE id=? AND lang=? ORDER BY created desc limit ".
            (static::$_history ? max([ intval(Core::lib("CMS")->purge), 1]) : 1).")",
        [$this->id, $this->lang, $this->id, $this->lang]);
        return true;
    }

/**
 * Sets a page parameter
 *
 * @param name
 * @param value
 */
    function setParameter($name, $value)
    {
        //! check input
        if(empty($this->id))
            throw new \Exception(L('No page id'));
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        if(substr($name,0,4)=="app.")
            $name=substr($name,4);
        if(substr($name,0,6)=="frame.")
            $name=substr($name,6);
        if(@$this->data[$name]!=$value) {
            //! write audit log
            Core::log('A',
                sprintf("Set page parameter %s for %s by %s",$name,$this->id,Core::$user->name).
                (Core::$core->runlevel > 2 ? " '".addslashes(strtr(@$this->data[$name],["\n"=>""]))."' -> '".
                addslashes(strtr($value,["\n"=>""]))."'":""), "cmsaudit");
            //! set parameter
            $this->data[$name] = $value;
        }
    }

/**
 * Save page meta information
 *
 * @param parameters
 * @param boolean new page
 */
    static function savePageInfo($params, $new=false)
    {
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        $rename=false;
        //! url checks
        if ($new) {
            if (!empty(DS::fetch("id", static::$_table, "id=? AND lang=?", "", "", [ $params['id'], $params['lang'] ]))) {
                Core::error(L("A page already exists with this url!"), "page.id");
                return false;
            }
        } else {
            //! if url changed
            if (!$new && !empty($params['pageid']) && $params['pageid'] != $params['id']) {
                $rename=true;
                DS::exec("UPDATE ".static::$_table." SET id=? WHERE id=?", [ $params['id'], $params['pageid'] ] );
                Core::log('A',sprintf("Page %s renamed to %s by %s",$params['pageid'],$params['id'],Core::$user->name), "cmsaudit");
            }
        }
        //! create page object
        $page = new self($params['id']);
        $needsave=false;
        foreach ($params as $k=>$v)
            if (property_exists($page, $k) && $page->$k!=$v) {
                Core::log('A',
                    sprintf("Set page %s for %s by %s",$k,$params['id'],Core::$user->name).
                    (Core::$core->runlevel > 2 ? " '".addslashes(strtr($page->$k,["\n"=>""]))."' -> '".
                addslashes(strtr($v,["\n"=>""]))."'":""), "cmsaudit");
                $page->$k = $v;
                $needsave=true;
            }
        //! save it
        if ($needsave && !$page->save($new)) {
            Core::error(L("Unable to save page!"));
			return false;
        } elseif($new || $rename)
            //! on successful new add and renames, redirect user to the new page
            die("<html><script>window.parent.document.location.href='".url($params['id'])."';</script></html>");
		return true;
    }

/**
 * Save Dynamic data sets for a page
 *
 * @param page id
 * @param dds array
 *
 * @return boolean success
 */
    static function saveDDS($pageid, $dds)
    {
        //! load new dds if given
        if (!empty($dds['_']['name']) && !empty($dds['_'][0])) {
            $k = $dds['_']['name'];
            unset($dds['_']['name']);
            $dds[$k] = $dds['_'];
        }
        unset($dds['_']);
        //! get page
        $page = new self($pageid);
        if(empty($dds) && empty($page->dds))
            return true;
        $old = $page->dds;
        //! update dds
        $page->dds = [];
        $needsave=false;
        foreach($dds as $k=>$d) {
            if (empty($d[0]) ||
                (@$d[0]!=@$old[$k][0]||@$d[1]!=@$old[$k][1]||
                @$d[2]!=@$old[$k][2]||@$d[3]!=@$old[$k][3]||@$d[4]!=@$old[$k][4])) {
                //! write audit log
                Core::log('A',sprintf("PageDDS %s for %s modified by %s",$k,$pageid,Core::$user->name).
                    (Core::$core->runlevel > 2 ?(empty($d[0])?" *deleted*":" '".implode("', '",$d)."'"):""), "cmsaudit");
                $needsave=true;
            }
            if (!empty($d[0])) {
                $page->dds[$k] = $d;
            }
        }
        return $page->save();
    }

/**
 * Function to save page lists
 *
 * @param name
 * @param array of page ids
 */
    static function savePageList($name, $pages)
    {
        //! check input
        if (empty($name))
            throw new \Exception(L('No pagelist name'));
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        if (is_string($pages))
            $pages = str_getcsv($pages, ',');
        //! write audit log
        Core::log('A',sprintf("Pagelist %s modified by %s",$name,Core::$user->name).
            (Core::$core->runlevel > 2 ?:" '".implode("', '",$pages)."'"), "cmsaudit");
        DS::exec("DELETE FROM ".static::$_table."_list WHERE list_id=?",[$name]);
        foreach($pages as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                DS::exec("INSERT INTO ".static::$_table."_list (list_id,page_id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
        return true;
    }

/**
 * Delete a page along with all history
 *
 * @param page id
 * @param created date of a specific version (optional)
 */
    static function delete($pageid,$created=null)
    {
        //! check input
        if(empty($pageid))
            throw new \Exception(L('No page id'));
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|webadm"))
            throw new \Exception(L('No user id'));
        $a = [ $pageid, Core::$client->lang ];
        if(!empty($created))
         $a[]=$created;
        //! write audit log
        Core::log('A',sprintf("Delete page %s (%s,%s) by %s",$pageid,Core::$client->lang,$created,Core::$user->name), "cmsaudit");
        $r = DS::exec(
            "DELETE FROM ".static::$_table." WHERE id=? AND (lang='' OR lang=?)".
            (!empty($created)?" AND created=?":""),
            $a
        ) > 0;
        if (!$r && substr($pageid,-1)=="/") {
            $a[0]=substr($pageid,0,strlen($pageid)-1);
            $r = DS::exec(
                "DELETE FROM ".static::$_table." WHERE id=? AND (lang='' OR lang=?)".
                (!empty($created)?" AND created=?":""),
                $a
            ) > 0;
        }
        return $r;
    }

/**
 * Get number of pages for a given template
 *
 * @param string template
 */
    static function getNum($template)
    {
        return DS::field("SUM(1)", static::$_table, "template=?", "", "",[$template]);
    }

/**
 * Get complex list of pages with user data
 *
 * @param boolean, return recently modified pages
 * @param array of view templates, filtering
 *
 * @return record set in an array
 */
    static function getPages($recent=0, $templates=[])
    {
        return DS::query("a.id,a.name,a.template as tid,a.lang,a.ownerid,".
              "a.modifyid,max(a.modifyd) as created,max(a.lockd) as lockd,count(1) as versions,min(max(a.publishid,-a.publishid)) as publishid,".
              "b.name as lockuser,c.name as moduser,v.name as template, CURRENT_TIMESTAMP as ct",
            "pages a left join users b on a.ownerid=b.id left join users c on a.modifyid=c.id left join views v on a.template=v.id",
            "(v.sitebuild='' OR v.sitebuild IS NULL)".(!empty($templates)?" AND a.template IN ('".implode("','",$templates)."')":""),
            "a.template,a.id",$recent?"created DESC":"a.template,a.name");
    }

    static function publish($ids)
    {
		if(empty(Core::lib("CMS")->revert))
			return;
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|pubadm"))
            throw new \Exception(L('No user id'));
		$ids[]="frame";
    	$pages = DS::query("id,lang,max(created) as created",static::$_table,"publishid=0 AND ownerid=0 AND id IN ('".implode("','",$ids)."')","id,lang");
		foreach($pages as $p) {
        	//! write audit log
        	Core::log('A',sprintf("Publicate page %s (%s,%s) by %s",$p['id'],$p['lang'],$p['created'],Core::$user->name), "cmsaudit");
			// mark newest as active
			DS::exec("UPDATE ".static::$_table." SET publishid=? WHERE id=? AND lang=? AND created=?",[Core::$user->id,$p['id'],$p['lang'],$p['created']]);
			// purge old active records
	        DS::exec("DELETE FROM ".static::$_table." WHERE id=? AND lang=? AND publishid!=0 AND created not in (SELECT created FROM ".static::$_table." WHERE id=? AND lang=? ORDER BY created desc limit ".
            max([ intval(Core::lib("CMS")->purge), 1]).")",
        [$p['id'], $p['lang'], $p['id'], $p['lang']]);
		}
		// delete intermediate versions
		DS::exec("DELETE FROM pages WHERE publishid=0 AND id IN ('".implode("','",$ids)."')");
    }

	static function cleanUp($pages=null)
	{
		//! check input
		if(!empty(Core::lib("CMS")->revert))
			return;
        if(empty(Core::$user->id) || !Core::$user->has("siteadm|pubadm"))
            throw new \Exception(L('No user id'));
		if(empty($pages))
			$pages = self::getPages();
		//! write audit log
		Core::log('A',sprintf("Purge page history by %s",Core::$user->name), "cmsaudit");
		//! purge old records
		foreach($pages as $p) {
			if($p['versions']>1)
				DS::exec("DELETE FROM ".static::$_table." WHERE id=? AND (lang='' OR lang=?) AND created!=?",[$p['id'],$p['lang'],$p['created']]);
		}
		//! make it published (without history that feature is off)
		DS::exec("UPDATE ".static::$_table." SET publishid=? WHERE publishid=0",[Core::$user->id]);
	}
}
