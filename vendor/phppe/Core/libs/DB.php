<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/.
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
 *
 * @author bzt
 * @date 1 Jan 2016
 * @brief A very basic SQL Query Builder, included in Pack
 * @todo Build sql according to selected driver, using $db->s loaded from db_(driver).php
 */

namespace PHPPE;

/**
 * Exception class.
 */
class DBException extends \Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Main class.
 */
class DB extends Extension
{
    private $command;
    private $table;
    private $deltable;
    private $fields = [];
    private $wheres = [];
    private $havings = [];
    private $groupBys = [];
    private $orderBys = [];
    private $offset = 0;
    private $limit = 0;

    /**
     * Alias of DS::like()
     *
     * @param string    user input
     *
     * @return string   sql-safe search ready like phrase
     */
    public static function like($str)
    {
        return DS::like($str);
    }
 
    /**
     * Define a select query with table.
     *
     * @param string    table name
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public static function select($table, $alias = null)
    {
        $n = new self();
        $n->command = 'SELECT';
        $n->table = $table.($alias ? ' '.$alias : '');

        return $n;
    }

    /**
     * Define an update command on table.
     *
     * @param string    table name
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public static function update($table, $alias = null)
    {
        $n = new self();
        $n->command = 'UPDATE';
        $n->table = $table.($alias ? ' '.$alias : '');

        return $n;
    }

    /**
     * Define an insert command into table.
     *
     * @param string    table name
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public static function insert($table, $alias = null)
    {
        $n = new self();
        $n->command = 'INSERT';
        $n->table = $table.($alias ? ' '.$alias : '');

        return $n;
    }

    /**
     * Define a replace command into table.
     *
     * @param string    table name
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public static function replace($table, $alias = null)
    {
        $n = new self();
        $n->command = 'REPLACE';
        $n->table = $table.($alias ? ' '.$alias : '');

        return $n;
    }

    /**
     * Define a table delete command.
     *
     * @param string    table name
     *
     * @return DB       instance
     */
    public static function delete($table, $alias = null)
    {
        $n = new self();
        $n->command = 'DELETE';
        $n->table = $table.($alias ? ' '.$alias : '');
        $n->deltable = $table;
        if ($alias) {
            $n->deltable = $alias;
        }

        return $n;
    }

    /**
     * Define a table truncate command.
     *
     * @param string    table name
     *
     * @return DB       instance
     */
    public static function truncate($table)
    {
        $n = new self();
        $n->command = 'TRUNCATE';
        $n->table = $table;

        return $n;
    }

    /**
     * Define a table multiplication command.
     *
     * @param string    table name
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public function table($table, $alias = '')
    {
        $this->table .= ', '.$table.($alias ? ' '.$alias : '');

        return $this;
    }

    /**
     * Define a table join command.
     *
     * @param string    join type ('left', 'right', 'inner', 'outter' etc.)
     * @param string    table name
     * @param string    on criteria
     * @param string    alias name for table
     *
     * @return DB       instance
     */
    public function join($type, $table, $on, $alias = '')
    {
        if (!in_array($type, ['INNER', 'CROSS', 'LEFT', 'RIGHT'.'OUTER', 'NATURAL LEFT', 'NATURAL RIGHT', 'NATURAL LEFT OUTER', 'NATURAL RIGHT OUTER'])) {
            throw new DBException(L('Bad join'));
        }
        $this->table .= ' '.strtoupper($type).' JOIN '.$table.($alias ? ' '.$alias : '').' ON '.(is_array($on) ? '('.implode(' AND ', $on).')' : $on);

        return $this;
    }

    /**
     * Add fields to query.
     *
     * @param string/array  fields
     *
     * @return DB           instance
     */
    public function fields($fields)
    {
        if (is_array($fields)) {
            $this->fields += $fields;
        } else {
            $this->fields += str_getcsv($fields, ',');
        }

        return $this;
    }

    /**
     * Add group by to query.
     *
     * @param string/array  fields
     *
     * @return DB           instance
     */
    public function groupBy($fields)
    {
        if (is_array($fields)) {
            $this->groupBys += $fields;
        } else {
            $this->groupBys += str_getcsv($fields, ',');
        }

        return $this;
    }

    /**
     * Add order by to query.
     *
     * @param string/array  fields
     *
     * @return DB           instance
     */
    public function orderBy($fields)
    {
        if (is_array($fields)) {
            $this->orderBys += $fields;
        } else {
            $this->orderBys += str_getcsv($fields, ',');
        }

        return $this;
    }

    // private helper
    private function condition($type, $wheres, $op = 'AND')
    {
        $op = strtoupper($op);
        if ($op != 'AND' && $op != 'OR') {
            throw new DBException(L('Bad logical operator specified').': '.$op);
        }
        if (is_array($wheres)) {
            $d = [];
            foreach ($wheres as $k => $v) {
                if (is_array($v)) {
                    if (empty($v[2]) && $v[1][0] != 'I' && $v[1][1] != 'S') {
                        throw new DBException(L('No right value').' #'.$k.': '.$v[0].' '.$v[1]);
                    }
                    if (!in_array(strtoupper($v[1]), ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'RLIKE', 'IS NULL', 'IS NOT NULL'])) {
                        throw new DBException(L('Bad conditional').' #'.$k.': '.$v[1]);
                    }
                    $d[] = $v[0].' '.strtoupper($v[1]).
                    (!empty($v[2]) ? ' '.
                        ($v[2] == '?' ? $v[2] :
                            "'".str_replace(["\r", "\n", "\t", "\x1a", "\x00"], ['\\r', '\\n', '\\t', '\\x1a', '\\x00'], addslashes(strtoupper($v[1]) == 'LIKE' ? DS::like($v[2]) : (is_array($v[2]) || is_object($v[2]) ? json_encode($v[2]) : $v[2])))."'")
                    : '');
                } else {
                    $d[] = $v;
                }
            }
            if (!empty($d)) {
                $this->$type[] = '('.implode(' '.strtoupper($op).' ', $d).')';
            }
        } else {
            $this->$type[] = $wheres;
        }

        return $this;
    }

    /**
     * Add where clause to query.
     *
     * @param string/array  wheres, array element can be [left,condition,right]
     * @param string        operator, 'AND' or 'OR'. Only used if array passed
     *
     * @return DB           instance
     */
    public function where($wheres, $op = 'AND')
    {
        return $this->condition("wheres", $wheres, $op);
    }

    /**
     * Add having clause to query.
     *
     * @param string/array  wheres, array element can be [left,condition,right]
     * @param string        operator, 'AND' or 'OR'. Only used if array passed
     *
     * @return DB           instance
     */
    public function having($wheres, $op = 'AND')
    {
        return $this->condition("havings", $wheres, $op);
    }

    /**
     * Set offset
     *
     * @param integer   offset
     *
     * @return DB       instance
     */
     public function offset($offs)
     {
        if($offs>=0)
            $this->offset = $offs;

        return $this;
     }

    /**
     * Set limit
     *
     * @param integer   limit
     *
     * @return DB       instance
     */
     public function limit($limit)
     {
        if($limit>=0)
            $this->limit = $limit;

        return $this;
     }

    /**
     * Build an sql sentance from object properties.
     *
     * @return string   sql sentance
     */
    public function sql()
    {
        //! common checks
        if (empty($this->table)) {
            throw new DBException(L('No table specified'));
        }
        if (empty($this->limit) && !empty($this->offset)) {
            throw new DBException(L('Offset without limit'));
        }
        //! build sql
        $sql = $this->command;
        //! command specific part
        switch ($this->command) {
            case 'SELECT' :
                $f = implode(',', $this->fields);
                $sql .= ' '.(!empty($f) ? $f : '*');
                $sql .= ' FROM '.$this->table;
            break;
            case 'UPDATE':
                if (empty($this->fields)) {
                    throw new DBException(L('No fields specified'));
                }
                $sql .= ' '.$this->table.' SET '.implode('=?,', $this->fields).'=?';
            break;
            case 'REPLACE':
            case 'INSERT':
                if (empty($this->fields)) {
                    throw new DBException(L('No fields specified'));
                }
                $sql .= ' INTO '.$this->table.' ('.implode(',', $this->fields).') VALUES (?'.str_repeat(',?', count($this->fields) - 1).')';
                if ($this->command == 'INSERT') {
                    $this->wheres = [];
                }
                // BUG in CodeCoverage. Marks elseif uncovered, while throw is covered...
                // @codeCoverageIgnoreStart
                elseif (empty($this->wheres)) {
                    // @codeCoverageIgnoreEnd
                    throw new DBException(L('No where specified'));
                }
            break;
            case 'DELETE':
                if ($this->table != $this->deltable) {
                    $sql .= ' '.$this->deltable;
                }
                $sql .= ' FROM '.$this->table;
            break;
            case 'TRUNCATE':
                $sql .= ' TABLE '.$this->table;
                $this->wheres = [];
            break;
            default:
                // @codeCoverageIgnoreStart
                throw new DBException(L('Unknown command').': '.$this->command);
                // @codeCoverageIgnoreEnd
        }
        //! add where clause
        if (count($this->wheres)) {
            $sql .= ' WHERE '.implode(' AND ', $this->wheres);
        }
        //! add group by
        if (count($this->groupBys)) {
            $sql .= ' GROUP BY '.implode(',', $this->groupBys);
        }
        //! add order by
        if (count($this->orderBys)) {
            $sql .= ' ORDER BY '.implode(',', $this->orderBys);
        }
        //! add having clause
        if (count($this->havings)) {
            $sql .= ' HAVING '.implode(' AND ', $this->havings);
        }
        //! add limit clause
        if (!empty($this->limit)) {
            $sql .= ' LIMIT '.intval($this->limit);
            if (!empty($this->offset)) {
                $sql .= ' OFFSET '.intval($this->offset);
            }
        }

        return $sql;
    }

    /**
     * Execute a query with a specific field set.
     *
     * @param array             values
     * @param integer           data source selector
     *
     * @return DB               instance
     */
    public function with($values, $ds = -1)
    {
        return      !is_array($values) || empty($values)
        ? $this->execute(null, $ds)         : $this->fields(array_keys($values))
                                              ->execute(array_values($values), $ds);
    }

    /**
     * Execute a query and return number of affected rows (commands) or data set (select query).
     *
     * @param array             arguments array, values for placeholders
     * @param integer           data source selector
     *
     * @return integer/array    number of affected rows or result set (array of assoc arrays)
     */
    public function execute($arguments = [], $ds = -1)
    {
        //! set data source if requested
        if ($ds != -1) {
            $old_ds = DS::ds();
            DS::ds($ds);
        }
        //! get sql sentance
        $sql = $this->sql();
        if (strpos($sql, '?') !== false && empty($arguments)) {
            throw new DBException(L('Placeholder(s) in SQL without argument').': '.$sql);
        }
        //! execute the query with PHPPE Core
        try {
            $ret = DS::exec($sql, $arguments);
            // @codeCoverageIgnoreStart
        } catch(\Exception $e) {
            throw new DBException(L($e->getMessage()).': '.$sql);
            // @codeCoverageIgnoreEnd
        }

        //! restore data source if changed
        if ($ds != -1) {
            DS::ds($old_ds);
        }
        //! return result
        return $ret;
    }

    /**
     * String representation of the object.
     * NOTE: __toString() not allowed to throw exception!
     *
     * @return string   sql sentance
     */
    public function __toString()
    {
        try {
            return $this->sql();
        } catch (\Exception $e) {
            return View::e('E', $e->getMessage(), 'DB');
        }
    }
}
