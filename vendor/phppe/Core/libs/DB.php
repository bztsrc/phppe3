<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/Core/libs/DB.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief A very basic SQL Query Builder, included in Pack
 */
namespace PHPPE;
use PHPPE\DS as DS;
use PHPPE\View as View;

/**
 * Exception class
 */
class DBException extends \Exception
{
    public function __construct($message="", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Main class
 */
class DB extends Extension
{
	private $command;
	private $table;
	private $deltable;
	private $fields = [];
	private $wheres = [];

/**
 * Define a select query with table
 *
 * @param table table name
 * @param alias alias name for table
 * @return DB instance
 */
	static function select($table,$alias=null)
	{
		$n = new self;
		$n->command = "SELECT";
		$n->table = $table . ($alias?" ".$alias:"");
		return $n;
	}

/**
 * Define an update command on table
 *
 * @param table table name
 * @param alias alias name for table
 * @return DB instance
 */
	static function update($table,$alias=null)
	{
		$n = new self;
		$n->command = "UPDATE";
		$n->table = $table . ($alias?" ".$alias:"");
		return $n;
	}

/**
 * Define an insert command into table
 *
 * @param table table name
 * @param alias alias name for table
 * @return DB instance
 */
	static function insert($table,$alias=null)
	{
		$n = new self;
		$n->command = "INSERT";
		$n->table = $table . ($alias?" ".$alias:"");
		return $n;
	}

/**
 * Define a replace command into table
 *
 * @param table table name
 * @param alias alias name for table
 * @return DB instance
 */
	static function replace($table,$alias=null)
	{
		$n = new self;
		$n->command = "REPLACE";
		$n->table = $table . ($alias?" ".$alias:"");
		return $n;
	}

/**
 * Define a table delete command
 *
 * @param table table name
 * @return DB instance
 */
	static function delete($table,$alias=null)
	{
		$n = new self;
		$n->command = "DELETE";
		$n->deltable = $n->table = $table;
		if($alias) $n->deltable=$alias;
		return $n;
	}

/**
 * Define a table truncate command
 *
 * @param table table name
 * @return DB instance
 */
	static function truncate($table)
	{
		$n = new self;
		$n->command = "TRUNCATE";
		$n->table = $table;
		return $n;
	}

/**
 * Define a table multiplication command
 *
 * @param table table name
 * @param alias alias name for table
 * @return DB instance
 */
	function table($table, $alias="")
	{
		$this->table .= ", ". $table . ($alias?" ".$alias:"");
		return $this;
	}

/**
 * Define a table join command
 *
 * @param type join type ('left', 'right', 'inner', 'outter' etc.)
 * @param table table name
 * @param on criteria
 * @param alias alias name for table
 * @return DB instance
 */
	function join($type, $table, $on, $alias="")
	{
		if(!in_array($type,["INNER","CROSS","LEFT","RIGHT"."OUTER","NATURAL LEFT","NATURAL RIGHT","NATURAL LEFT OUTER","NATURAL RIGHT OUTER"]))
			throw new DBException(L("Bad join"));
		$this->table .= " ".strtoupper($type)." JOIN ".$table . ($alias?" ".$alias:"")." ON ".(is_array($on)?"(".implode(" AND ",$on).")":$on);
		return $this;
	}

/**
 * Add fields to query
 *
 * @param fields array or string
 * @return DB instance
 */
	function fields($fields)
	{
		if(is_array($fields))
			$this->fields += $fields;
		else
			$this->fields[] = $fields;
		return $this;
	}

/**
 * Add where clause to query
 *
 * @param wheres array or string, array elemnt can be [left,condition,right]
 * @param op operator, 'AND' or 'OR'. Only used if array passed
 * @return DB instance
 */
	function where($wheres,$op="AND")
	{
		if($op!="AND"&&$op!="OR")
			throw new DBException(L("Bad logical operator specified").": ".$op);
		if(is_array($wheres)) {
			$d=[];
			foreach($wheres as $k=>$v) {
				if(is_array($v)) {
					if(empty($v[2])&&$v[1][0]!='I'&&$v[1][1]!='S')
						throw new DBException(L("No right value")." #".$k.": ".$v[0]." ".$v[1]);
					if(!in_array($v[1],["=","!=","<","<=",">",">=","LIKE","RLIKE","IS NULL","IS NOT NULL"]))
						throw new DBException(L("Bad conditional")." #".$k.": ".$v[1]);
					$d[]=$v[0]." ".$v[1].
					(!empty($v[2])?" ".
						($v[2]=="?"?$v[2]:
							"'".str_replace(["\r","\n","\t","\x1a","\x00"],["\\r","\\n","\\t","\\x1a","\\x00"],addslashes($v[1]=="LIKE"?DS::like($v[2]):(is_array($v[2])||is_object($v[2])?json_encode($v[2]):$v[2])))."'")
					:"");
				}
			}
			$this->wheres[] = "(".implode(" ".strtoupper($op)." ",$d).")";
		} else
			$this->wheres[] = $wheres;
		return $this;
	}

/**
 * Build an sql sentance from object properties
 *
 * @return sql sentance
 */
	function sql()
	{
		//! common checks
		if(empty($this->command))
			throw new DBException(L("No command specified"));
		if(empty($this->table))
			throw new DBException(L("No table specified"));
		//! build sql
		$sql = $this->command;
		//! command specific part
		switch($this->command) {
			case "SELECT":
				$f = implode(",",$this->fields);
				$sql.= " ".(!empty($f)?$f:"*");
				$sql.= " FROM ".$this->table;
			break;
			case "UPDATE":
				if(empty($this->fields))
					throw new DBException(L("No fields specified"));
				$sql.= " ".$this->table." SET ".implode("=?,",$this->fields)."=?";
			break;
			case "REPLACE":
			case "INSERT":
				if(empty($this->fields))
					throw new DBException(L("No fields specified"));
				$sql.=" INTO ". $this->table. " (".implode(",",$this->fields).") VALUES (?".str_repeat(",?",count($this->fields)-1).")";
				if($this->command=="INSERT")
					$this->wheres=[];
				elseif(empty($this->wheres))
					throw new DBException(L("No where specified"));
			break;
			case "DELETE":
				if($this->table != $this->deltable)
					$sql.=" ".$this->deltable;
				$sql.=" FROM ".$this->table;
			break;
			case "TRUNCATE":
				$sql.=" TABLE ".$this->table;
				$this->wheres=[];
			break;
			default:
				throw new DBException(L("Unknown command").": ".$this->command);
		}
		//! add where clause
		if(count($this->wheres))
			$sql.= " WHERE ".implode(" AND ",$this->wheres);
		return $sql;
	}

/**
 * Execute a query and return number of affected rows (commands) or data set (select query)
 *
 * @param arguments array, values for placeholders
 * @param ds data source selector
 * @return integer or array of assoc arrays
 */
	function execute($arguments=[],$ds=-1)
	{
		//! set data source if requested
		if($ds!=-1) {
			$old_ds=DS::ds();
			DS::ds($ds);
		}
		//! get sql sentance
		$sql = $this->sql();
		if(strpos($sql,"?")!==false && empty($arguments))
			throw new DBException(L("Placeholder(s) in SQL without argument").": ".$sql);
		//! execute the query with PHPPE Core
		$ret = DS::exec($sql,$arguments);
		//! restore data source if changed
		if($ds!=-1)
			DS::ds($old_ds);
		//! return result
		return $ret;
	}

/**
 * String representation of the object.
 * NOTE: __toString() not allowed to throw exception!
 *
 * @return sql sentance
 */
	function __toString()
	{
		try {
			return $this->sql();
		} catch(\Exception $e) {
			return View::e("E","DB",$e->getMessage());
		}
	}
}
