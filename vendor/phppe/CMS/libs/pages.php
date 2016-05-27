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
        //! write audit log
        \PHPPE\Core::log('A',
            sprintf(L("Set page parameter %s for %s by %s"),$name,$this->id,\PHPPE\Core::$user->name).
            (\PHPPE\Core::$core->runlevel > 2 ? " '".addslashes(strtr(@$this->data[$name],["\n"=>""]))."' -> '".
            addslashes(strtr(@$this->data[$name],["\n"=>""]))."'":""), "cmsaudit");
        //! set parameter
        $this->data[$name] = $value;
        //! save to database
        return $this->save();
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
        if (static::$_history)
            $this->created = $this->modifyd;
        //! build the arguments array
        $a = [];
        foreach ($this as $k => $v) {
            if ($k[0] != '_') {
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
            $pages = \PHPPE\Core::x(",", $pages);
        \PHPPE\DS::exec("DELETE FROM pages_list WHERE list_id=?",[$name]);
        foreach($pages as $k=>$v)
            if(!empty($v)&&trim($v)!="null")
                \PHPPE\DS::exec("INSERT INTO pages_list (list_id,page_id,ordering) values (?,?,?)",[$name,$v,intval($k)]);
        return true;
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
                "a.id!='frame'".(!empty($templates)?" AND a.template IN ('".implode("','",$templates)."')":""),
                "a.template,a.id",$recent?"created DESC":"a.template,a.name");
    }
}
