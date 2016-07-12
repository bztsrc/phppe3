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
 * @file vendor/phppe/Core/libs/Registry.php
 *
 * @author bzt
 * @date 1 Jan 2016
 * @brief key-value registry for Extension configuration, included in Pack
 */

namespace PHPPE;

class Registry extends Extension
{
    /**
     * Read a parameter value for key from registry. Will return default if key not found.
     *
     * @param string    key
     * @param mixed     optional default value
     *
     * @return mixed    value
     */
    public static function get($key, $default = '')
    {
        //sanitize key
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        $value = null;
        //try to read from database...
        try {
            $value = DS::field('data', 'registry', 'name=?', '', '', [$key]);
        } catch (\Exception $e) {
            //...fallback to files
            $v = trim(@file_get_contents('data/registry/'.$key));
            $value = json_decode($v);
            if (!is_array($value) && !is_object($value)) $value = $v;
        }

        return $value == null ? $default : $value;
    }

    /**
     * Store a parameter value for key into registry.
     *
     * @param string    key
     * @param mixed     value
     */
    public static function set($key, $value)
    {
        //sanitize key
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        $value = is_array($value) || is_object($value) ? json_encode($value) : trim($value);
        //try to save to database...
        try {
            if (!DS::exec('REPLACE INTO registry (name,data) VALUES (?,?)', [$key, $value])) {
                //!if exec returns 0 records updated somehow...
                // @codeCoverageIgnoreStart
                throw new \Exception();
            }
                // @codeCoverageIgnoreEnd
            else {
                return true;
            }
        } catch (\Exception $e) {
            //...fallback to files
            @mkdir('data/registry');

            return file_put_contents('data/registry/'.$key, $value) > 0 ? true : false;
        }
    }

    /**
     * Remove a parameter from registry.
     *
     * @param string    key
     */
    public static function del($key)
    {
        //sanitize key
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        //remove both database record as well as file
        try {
            @DS::exec('DELETE FROM registry WHERE name=?', [$key]);
        } catch (\Exception $e) {
        }
        @unlink('data/registry/'.$key);
    }
}
