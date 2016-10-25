<?php
/**
 * minimal view model for ORM
 */
namespace PHPPE;

class Views extends \PHPPE\Model
{
    public $ctrl = "";
    public $data = "";
    public $jslib = [];
    public $css = [];
    public $sitebuild = "";

    static $_table = "views";

/**
 * Save layout
 *
 * @param force use of insert instead of update
 *
 * @return boolean success
 */
    function save($force=false)
    {
        //! check input
        if(empty(\PHPPE\Core::$user->id) || !\PHPPE\Core::$user->has("siteadm"))
            throw new \Exception(L('No user id'));
        //! set up properties
        $this->modifyd = date("Y-m-d H:i:s", \PHPPE\Core::$core->now);
        $this->modifyid = \PHPPE\Core::$user->id;
        \PHPPE\Core::log('A',sprintf("Layout %s modified by %s",$this->id,\PHPPE\Core::$user->name), "cmsaudit");
        return parent::save($force);
    }

/**
 * Delete layout
 */
    public function delete()
    {
        //! check input
        if(empty($this->id))
            throw new \Exception(L('No layout id'));
        if(empty(\PHPPE\Core::$user->id) || !\PHPPE\Core::$user->has("siteadm"))
            throw new \Exception(L('No user id'));
        \PHPPE\Core::log('A',sprintf("Layout %s deleted by %s",$this->id,\PHPPE\Core::$user->name), "cmsaudit");
        \PHPPE\DS::exec("DELETE FROM ".static::$_table." WHERE id=?",[$this->id]);
        \PHPPE\DS::exec("DELETE FROM ".\PHPPE\Page::$_table."_list WHERE page_id IN (SELECT id FROM pages WHERE template=?)",[$this->id]);
        \PHPPE\DS::exec("DELETE FROM ".\PHPPE\Page::$_table." WHERE template=?",[$this->id]);
    }
}
