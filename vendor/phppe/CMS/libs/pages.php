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
    function __construct($id="")
    {
        if(!empty($id)) {
            $this->id = $id;
            $this->load($id,"","created DESC");
        }
        static::$_history = !empty(\PHPPE\Core::lib("CMS")->revert);
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
            throw new \Exception(L('No page id'));
        if(empty(\PHPPE\Core::$user->id))
            throw new \Exception(L('No user id'));
        //! release old locks (locked for more than 10 minutes)
        \PHPPE\DS::exec("UPDATE ".static::$_table." SET ownerid=0 WHERE lockd<?",
            [ date("Y-m-d H:i:s", \PHPPE\Core::$core->now-600) ]);
        //! try to lock with one atomic query
        \PHPPE\DS::exec("UPDATE ".static::$_table.
            " SET ownerid=?,lockd=CURRENT_TIMESTAMP WHERE id=? AND lang=? AND created=? AND ownerid=0",
            [\PHPPE\Core::$user->id, $this->id, $this->lang, $this->created]);
        //! get the new owner
        $this->ownerid = \PHPPE\DS::field(
            "ownerid", "pages", "id=? AND lang=?", "", "created DESC",
            [$this->id, $this->lang]);
        //! success if it's the current user
        return $this->ownerid == \PHPPE\Core::$user->id;
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
        return \PHPPE\DS::exec("UPDATE ".static::$_table.
            " SET ownerid=0,lockd=0 WHERE id=? AND lang=? AND created=?",
            [ $this->id, $this->lang, $this->created]) > 0;
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
        return \PHPPE\DS::exec("UPDATE ".static::$_table." SET ownerid=0 WHERE ownerid=?",
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
        if (empty($this->id))
            throw new \Exception(L('No page id'));
        if (empty(\PHPPE\Core::$user->id))
            throw new \Exception(L('No user id'));
        $d = \PHPPE\DS::db();
        if (empty($d))
            throw new \Exception('no ds');
        //! set up properties
        $this->ownerid = 0;
        $this->lockd = "";
        $this->modifyd = date("Y-m-d H:i:s", \PHPPE\Core::$core->now);
        $this->modifyid = \PHPPE\Core::$user->id;
        if (static::$_history || $force)
            $this->created = $this->modifyd;
        //! build the arguments array
        $a = [];
        foreach ($this as $k => $v) {
            if ($k[0] != '_' && $k!="created") {
                $a[$k] = is_scalar($v) ? $v : json_encode($v);
            }
        }
        //! save page
        if (!\PHPPE\DS::exec((!static::$_history && !$force ?
            'UPDATE '.static::$_table.' SET '.implode('=?,', array_keys($a)).
                '=? WHERE id='.$d->quote($this->id).' AND lang='.$d->quote($this->lang) :
            'INSERT INTO '.static::$_table.' ('.implode(',', array_keys($a)).') VALUES (?'.str_repeat(',?', count($a) - 1).')'),
            array_values($a)))
            return false;
        //! purge old records
        \PHPPE\DS::exec("DELETE FROM ".static::$_table." WHERE id=? AND lang=? AND created not in (SELECT created FROM pages WHERE id=? AND lang=? ORDER BY created desc limit ".
            (static::$_history ? max([ intval(\PHPPE\Core::lib("CMS")->purge), 1]) : 1).")",
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
        if(substr($name,0,4)=="app.")
            $name=substr($name,4);
        if(substr($name,0,6)=="frame.")
            $name=substr($name,6);
        //! write audit log
        \PHPPE\Core::log('A',
            sprintf(L("Set page parameter %s for %s by %s"),$name,$this->id,\PHPPE\Core::$user->name).
            (\PHPPE\Core::$core->runlevel > 2 ? " '".addslashes(strtr(@$this->data[$name],["\n"=>""]))."' -> '".
            addslashes(strtr(@$this->data[$name],["\n"=>""]))."'":""), "cmsaudit");
        //! set parameter
        $this->data[$name] = $value;
    }

/**
 * Save page meta information
 *
 * @param parameters
 * @param boolean new page
 */
    static function savePageInfo($params, $new=false)
    {
        $rename=false;
        //! url checks
        if ($new) {
            if (!empty(\PHPPE\DS::fetch("id", static::$_table, "id=?", "", "", [ $params['id'] ]))) {
                \PHPPE\Core::error(L("A page already exists with this url!"), "page.id");
                return false;
            }
        } else {
            //! if url changed
            if (!$new && !empty($params['pageid']) && $params['pageid'] != $params['id']) {
                $rename=true;
                \PHPPE\DS::exec("UPDATE ".static::$_table." SET id=? WHERE id=?", [ $params['id'], $params['pageid'] ] );
            }
        }
        //! create page object
        $page = new self($params['id']);
        foreach ($params as $k=>$v)
            if (property_exists($page, $k))
                $page->$k = $v;
        //! save it
        if (!$page->save($new))
            \PHPPE\Core::error(L("Unable to save page!"));
        elseif($new || $rename)
            //! on successful new add and renames, redirect user to the new page
            die("<html><script>window.parent.document.location.href='".url($params['id'])."';</script></html>");
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
        //! update dds
        $page->dds = [];
        foreach($dds as $k=>$d)
            if (!empty($d[0]))
                $page->dds[$k] = $d;
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
        if (is_string($pages))
            $pages = str_getcsv($pages, ',');
        \PHPPE\DS::exec("DELETE FROM ".static::$_table."_list WHERE list_id=?",[$name]);
        foreach($pages as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                \PHPPE\DS::exec("INSERT INTO ".static::$_table."_list (list_id,page_id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
        return true;
    }

/**
 * Delete a page along with all history
 *
 * @param page id
 */
    static function delete($pageid)
    {
        //! check input
        if(empty($pageid))
            throw new \Exception(L('No page id'));
        $r = \PHPPE\DS::exec(
            "DELETE FROM ".static::$_table." WHERE id=? AND (lang='' OR lang=?)",
            [ $pageid, \PHPPE\Core::$client->lang ]
        ) > 0;
        if (!$r && substr($pageid,-1)=="/")
        $r = \PHPPE\DS::exec(
            "DELETE FROM ".static::$_table." WHERE id=? AND (lang='' OR lang=?)",
            [ substr($pageid,0,strlen($pageid)-1), \PHPPE\Core::$client->lang ]
        ) > 0;
        return $r;
    }

/**
 * Get number of pages for a given template
 *
 * @param string template
 */
    static function getNum($template)
    {
        return \PHPPE\DS::field("SUM(1)", static::$_table, "template=?", "", "",[$template]);
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
        return \PHPPE\DS::query("a.id,a.name,a.template as tid,a.lang,a.dds,a.ownerid,a.modifyid,max(a.modifyd) as created,max(a.lockd) as lockd,count(1) as versions,b.name as lockuser,c.name as moduser,v.name as template, CURRENT_TIMESTAMP as ct",
                "pages a left join users b on a.ownerid=b.id left join users c on a.modifyid=c.id left join views v on a.template=v.id",
                "(v.sitebuild='' OR v.sitebuild IS NULL)".(!empty($templates)?" AND a.template IN ('".implode("','",$templates)."')":""),
                "a.template,a.id",$recent?"created DESC":"a.template,a.name");
    }
}
