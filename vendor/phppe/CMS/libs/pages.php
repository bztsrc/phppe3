<?php
/**
 * minimal page model for ORM
 */
namespace PHPPE;
use \PHPPE\Core as PHPPE;

class Page extends \PHPPE\Model
{
	public $data='';
	public $ctrl='';
	static $_table="pages";

	static function getPages($recent=0)
	{
		return PHPPE::query("a.id,a.name,a.template as tid,a.lang,a.dds,a.ownerid,a.modifyid,max(a.modifyd) as created,max(a.lockd) as lockd,count(1) as versions,b.name as lockuser,c.name as moduser,v.name as template, CURRENT_TIMESTAMP as ct",
				"pages a left join users b on a.ownerid=b.id left join users c on a.modifyid=c.id left join views v on a.template=v.id",
				"a.id!='frame'","a.template,a.id",$recent?"created DESC":"a.template,a.name");
	}
}
