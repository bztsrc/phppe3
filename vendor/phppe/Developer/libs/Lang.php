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
		echo(chr(27)."[96m".L("Usage").":".chr(27)."[0m\n  php public/index.php ".Core::$core->app." <Extension> [language [--write|--write-all]]\n\n".
			chr(27)."[96m".L("If language not given, detects strings in code, otherwise merges language array. With --write it will store the dictionary. If you specify --write-all, then unused phrases won't be prefixed.").chr(27)."[0m\n\n");
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
            'tests/*.php', 'tests/*/*.php', 'tests/*/*/*.php', 'tests/*/*/*/*.php',
            'js/*.js', 'js/*/*.js', 'js/*/*/*.js',
            'js/*.php', 'js/*/*.php', 'js/*/*/*.php',
            'views/*.tpl','sql/*.sql'
            ] as $v) {
                $D += array_fill_keys(glob('vendor/phppe/'.$extension.'/'.$v), 0);
        }
        $composer=json_decode(@file_get_contents("vendor/phppe/".$extension."/composer.json"), true);
        //! small hack for the Core
        $K=[];
        if ($extension=="Core") {
            //! for Core, also look up phrases in the main file
            $D[file_exists("public/source.php")?"public/source.php":"public/index.php"]=0;
        } else {
            //! don't collect phrases that are specified in a required extension
            //! assume Core is always loaded
            $composer["require"]["phppe/Core"]=1;
            echo(chr(27)."[96m".L("Include").": ");
            foreach($composer["require"] as $k=>$v) {
                $a = @include("vendor/".$k."/lang/".(!empty($lang)?$lang:"en").".php");
                if(is_array($a)) {
                    $K = array_merge($K,$a);
                    echo(chr(27)."[92m");
                } else {
                    echo(chr(27)."[91m-");
                }
                echo($k." ");
            }
            echo("\r\n");
        }

        echo(chr(27)."[96m".L("Files").":".chr(27)."[92m ".count($D).chr(27)."[0m\r\n".L("Reading")."...\r");

        //! iterate on list
        foreach ($D as $fn => $v) {
            //! load code
            $d = @file_get_contents($fn);
            //! look for L() calls
            $i = 0; $line=1;
            $l = strlen($d);
            while ($i < $l) {
                if ($d[$i] == "\n")
                    $line++;
               //! skip over string literals
/*
                if (($d[$i] == "'" || $d[$i] == '"') && $d[$i-1]!='\\') {
                    $s = $d[$i];
                    $j = $i;
                    $i++;
                    while ($i < $l && $d[$i] != $s) {
                        if ($d[$i] == "\n")
                            $line++;
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
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    continue;
                }
*/

                //! check for calls
                $e="";
                if (!preg_match("/[a-z0-9_\$]/i",@$d[$i-1]) && substr($d,$i,2)=="L(") {
                    $i+=2; while($i<$l && ($d[$i]==' '||$d[$i]=="\t"||$d[$i]=="\n"||$d[$i]==')')) {
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    if($d[$i]=="'" || $d[$i]=='"') { $e=$d[$i]; $i++; }
                }
                if (substr($d,$i,4)=="<!L ") { $e=">"; $i+=4; }
                //! second argument to addon() will be translated as well
                if (substr($d,$i,12)=="Core::addon(") {
                    $i+=12; while($i<$l && $d[$i]!=',') {
                        if ($d[$i] == ")")
                            continue 2;
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    $i++; while($i<$l && ($d[$i]==' '||$d[$i]=="\t"||$d[$i]=="\n")) {
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    if($d[$i]=="'" || $d[$i]=='"') { $e=$d[$i]; $i++; }
                }
                //! add access control entries to translations too
                if (substr($d,$i,10)=="user->has("||substr($d,$i,9)=="user.has(") {
                    $i+=$d[$i+4]=="."?9:10;
                    if($d[$i]=="'" || $d[$i]=='"') { 
                        $e=$d[$i]; $i++; $k=$i;
                        while($i<$l && $d[$i]!=$e) {
                            $i++;
                        }
                        $e="";
                        foreach(explode("|",substr($d,$k,$i-$k)) as $g) {
                            //! avoid notice when appending filenames and line numbers
                            if (!isset($L[$g]) || !empty($lang))
                                $L[$g] = "";
                            //! add words or file and line if language not given
                            $L[$g] .= empty($lang)?
                                (empty($L[$g])?"":", ").$fn.":".$line :
                                $g;
                        }
                    }
                     while($i<$l && $d[$i]!=$e) {
                        if ($d[$i] == ")")
                            continue 2;
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    $i++; while($i<$l && ($d[$i]==' '||$d[$i]=="\t"||$d[$i]=="\n")) {
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    if($d[$i]=="'" || $d[$i]=='"') { $e=$d[$i]; $i++; }
                }
                //! should we look for an ending character?
                if($e) {
                    $k=$i;while($i<$l&&$d[$i]!=$e) {
                        if ($d[$i]=='$') continue 2;
                        if ($d[$i] == '\\') {
                            $i++;
                        }
                        if ($d[$i] == "\n")
                            $line++;
                        $i++;
                    }
                    $g=substr($d,$k,$i-$k);
                    if($d[$i+1]!="+" && $d[$i+1]!="."){
                        //! avoid notice when appending filenames and line numbers
                        if (!isset($L[$g]) || !empty($lang))
                            $L[$g] = "";
                        //! add words or file and line if language not given
                        $L[$g] .= empty($lang)?
                            (empty($L[$g])?"":", ").$fn.":".$line :
                            $g;
                    }
                    if ($d[$i] == "\n")
                        $line++;
                }
                $i++;
            }
        }
        
        $dups=array_flip(array_intersect(array_keys($L), array_keys($K)));
        echo(chr(27)."[96m".L("Phrases").": ".chr(27)."[92m ".count($L).chr(27)." ".chr(27)."[0m (".count($dups)." / ".count($K).")".chr(27)."[0m\r\n");

        //! without language, dump the results
        if (empty($lang)) {
            foreach($L as $k=>$v) {
                echo((!isset($K[$k])?chr(27)."[92m":"")."  ".(strlen($k)<40?sprintf("%-40s",$k):substr($k,0,80)."\r\n   ").chr(27)."[0m ".$v."\r\n");
            }
        } else {
            //! merge with language dictionary
            $dict="vendor/phppe/".$extension."/lang/".$lang.".php";
            @mkdir(dirname($dict));
            $extra=[]; $l=[];
            //! add extension name and description
            $a=(!empty($lang)?$lang:"en");
            $b=explode("/",$composer['name'])[1];
            $l[$b]=!empty($composer['name_'.$a])?$composer['name_'.$a]:(!empty($composer['name_en'])?$composer['name_en']:$composer['name']);
            $l['_'.$b]=!empty($composer['description_'.$a])?$composer['description_'.$a]:$composer['description'];

            //! require() would miss a few translations
            $was2 = eval(strtr(@file_get_contents($dict), [ "//-"=>"", "<"."?php"=>"" ] ));
            if(!is_array($was2)) {
                $was=$was2=[];
            }
            foreach($was2 as $k=>$v)
                $was[stripslashes($k)]=stripslashes($v);
            unset($was2);
            $l=array_merge($l,$was);
            //! if found, merge. Otherwise use the new one
            if (is_array($l)) {
                $extra=array_flip(array_diff(array_keys($l), array_keys($L)));
                foreach ($L as $k=>$v)
                    if (!isset($l[$k]))
                        $l[$k] = $v;
            } else
                $l = $L;
            unset($extra['rtl']);
            unset($extra[$b]);
            unset($extra['_'.$b]);
            //! generate php output
            $out="<"."?php\nreturn [\n";
            foreach($l as $k=>$v) {
                if(isset($K[$k]))
                    continue;
                if(empty($write))
                    $out.=(isset($extra[$k])?chr(27)."[91m":(!isset($was[$k])?chr(27)."[92m":""));
                $out.=((isset($extra[$k])&&$write!="--write-all")||isset($K[$k])?"//-":"")."\t\"".str_replace("\"","\\\"",$k)."\" => \"".str_replace("\"","\\\"",$v)."\",\n";
                if(empty($write))
                    $out.=chr(27)."[0m";
            }
            $out.="];\n";
            //! if last argument given, save the results
            if ($write=="--write"||$write=="--write-all") {
                file_put_contents($dict, $out, LOCK_EX);
                echo(chr(27)."[96mModified (".chr(27)."[92m".count($l).chr(27)."[96m): ".chr(27)."[0m".$dict."\n");
            } else
                echo($out);
        }
	}
}
