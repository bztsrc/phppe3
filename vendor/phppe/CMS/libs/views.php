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

	public function delete()
	{
		if(!empty($this->id)){
			\PHPPE\Core::log('A',sprintf(L("Layout %s deleted by %s"),$this->id,\PHPPE\Core::$user->name), "cmsaudit");
			\PHPPE\DS::exec("DELETE FROM ".static::$_table." WHERE id=?",[$this->id]);
			\PHPPE\DS::exec("DELETE FROM pages_list WHERE page_id IN (SELECT id FROM pages WHERE template=?)",[$this->id]);
			\PHPPE\DS::exec("DELETE FROM pages WHERE template=?",[$this->id]);
		}
	}
}
