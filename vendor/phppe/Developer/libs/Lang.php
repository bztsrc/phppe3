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
 * @file vendor/phppe/Developer/libs/Lang.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Utility to create language files
 */
namespace PHPPE;

class Lang
{
/**
 * Usage information
 * @usage php public/index.php create
 */
	static function getUsage()
	{
		echo(L("Usage").":\n  php public/index.php ".\PHPPE\Core::$core->app." <Extension> [language [--write]]\n\n".
			L("If language not given, detects strings in code, otherwise merges language array.")."\n\n");
	}


/**
 * Parse php code for translatable strings and merge with language dictionary
 * @usage php public/index.php lang <extension> [languagecode]
 *
 * @param extension name
 * @param two letter language code
 * @param the flag "--write"
 */
	static function parse($extension, $lang="", $write="")
	{
        //! sanitize input
        $extension = strtr($extension, [ "."=>"", "/"=>"" ]);
        $lang = strtr($lang, [ "."=>"", "/"=>"" ]);

        //! language keys
        $L = [];
        //! get files
        $D = [];
        foreach (['*.php',
            'addons/*.php', 'addons/*/*.php', 'addons/*/*/*.php', 'addons/*/*/*/*.php',
            'ctrl/*.php', 'ctrl/*/*.php', 'ctrl/*/*/*.php', 'ctrl/*/*/*/*.php',
            'libs/*.php', 'libs/*/*.php', 'libs/*/*/*.php', 'libs/*/*/*/*.php', 'libs/*/*/*/*/*.php', 'libs/*/*/*/*/*/*.php',
            'js/*.js', 'js/*/*.js', 'js/*/*/*.js',
            'js/*.php', 'js/*/*.php', 'js/*/*/*.php',
            'views/*.tpl'
            ] as $v) {
                $D += array_fill_keys(glob('vendor/phppe/'.$extension.'/'.$v), 0);
        }
        //! small hack for the Core
        if ($extension=="Core")
            $D["public/index.php"]=0;

        echo("Files: ".count($D)."\n");

        //! iterate on list
        foreach ($D as $fn => $v) {
            //! load code
            $d = @file_get_contents($fn);
            //! look for L() calls
            $i = 0;
            $l = strlen($d);
            while ($i < $l) {
               //! skip over string literals
                if (($d[$i] == "'" || $d[$i] == '"')) {
                    $s = $d[$i];
                    $j = $i;
                    ++$i;
                    while ($i < $l && $d[$i] != $s) {
                        if ($d[$i] == '\\') {
                            $i++;
                        }
                        $i++;
                    }
                    $i++;
                    continue;
                }
                 //! don't take comments into account
                if ($d[$i] == '/' && $d[$i + 1] == '*') {
                    $s = $i;
                    $i += 2;
                    while ($i + 1 < $l && ($d[$i] != '*' || $d[$i + 1] != '/')) {
                        $i++;
                    }
                    $i += 2;
                    continue;
                }
                //! check for calls
                $e="";
                if (!preg_match("/[a-z0-9_\$]/i",@$d[$i-1]) && substr($d,$i,2)=="L(") {
                    $i+=2; while($i<$l && ($d[$i]==' '||$d[$i]=="\t"||$d[$i]=="\n")) $i++;
                    if($d[$i]=="'" || $d[$i]=='"') { $e=$d[$i]; $i++; }
                }
                if (substr($d,$i,4)=="<!L ") { $e=">"; $i+=4; }
                if($e) {
                    $k=$i;while($i<$l&&$d[$i]!=$e) {
                        if ($d[$i]=='$') continue 2;
                        if ($d[$i] == '\\') {
                            $i++;
                        }
                        $i++;
                    }
                    $L[substr($d,$k,$i-$k)] = substr($d,$k,$i-$k);
                }
                $i++;
            }
        }

        if (empty($lang)) {
            print_r($L);
        } else {
            //! merge with language dictionary
            $dict="vendor/phppe/".$extension."/lang/".$lang.".php";
            $l = @require($dict);
            $extra=[];
            if (is_array($l)) {
                $extra=array_flip(array_diff(array_keys($l), array_keys($L)));
                foreach ($L as $k=>$v)
                    if (empty($l[$k]))
                        $l[$k] = $v;
            } else
                $l = $L;
            $out="<"."?php\nreturn [\n";
            foreach($l as $k=>$v) {
                $out.=(!empty($extra[$k])?"/*+++*/":"")."\t\"".addslashes($k)."\" => \"".addslashes($v)."\",\n";
            }
            $out.="];\n";
            if ($write=="--write") {
                file_put_contents($dict, $out, LOCK_EX);
                echo("Modified (".count($l)."): ".$dict."\n");
            } else
                echo($out);
        }
	}
}
