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
 * @file vendor/phppe/Developer/libs/Repository.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Utilities to create a repository (packages.json)
 */
namespace PHPPE;

class Repository
{
	//! PHPPE core source and deployment file path
	static $sourceFile="public/source.php";
	static $deployFile="public/index.php";
	//! self test page
	static $selfTestFile="vendor/phppe/Core/views/index.tpl";
	//! documentation
	static $docFile="public/index.html";
	//! This can be overridden from command line
	static $repoBase="https://bztsrc.github.io/phppe3/";
	//static $repoBase="https://raw.githubusercontent.com/bztsrc/phppe3/master/";
	static $bnsBase="https://phppe.org/business/";
	//! valid extension categories
	static $categories=["Connections","Content","Security","Business","Sales","Office","Games","Banners","Hardware","User Input"];

/**
 * Compress source to deployment format
 * @usage php public/index.php minify
 */
	static function compress()
	{
		echo(chr(27)."[96mCompressing index.php: ".chr(27)."[0m");
		$data=file_get_contents(self::$sourceFile);
		if(empty($data))
			die(chr(27)."[91munable to read ".self::$sourceFile.chr(27)."[0m\n");
		//! uncomment self check
		$data=str_replace('//$c=__FILE__;if(filesize','$c=__FILE__;if(filesize',$data);
		//! remove benchmarking code
		$data=preg_replace('|/\*! BENCHMARK START \*/.*?/\*! BENCHMARK END \*/|ims',"",$data);
		$data=preg_replace('/(self|Core)::bm\([^;]+;/ims',"",$data);
		//! keep license comment
		$i=strpos($data,"*/")+2;
		//! make sure minifier is not turned off
		Core::$core->nominify=false;
		$code=substr($data,$i);
		$out=substr($data,0,$i)."\n".Assets::minify($code,"php");
		$l=strlen($out);
		if($l>99999)
			die(chr(27)."[91mfile too big, ".$l." bytes".chr(27)."[0m\n");
		//! replace file size
		$out=str_replace("c)!=99999||'","c)!=".sprintf("%5d",$l)."||'",$out);
		//! calculate new checksum
		$chksum=sha1(preg_replace("/\'([^\']+)\'\!\=sha1/","''!=sha1",$out));
		$out=preg_replace("/\'([^\']+)\'\!\=sha1/","'".$chksum."'!=sha1",$out);
		$old=@filesize(self::$deployFile);
		//! write out deployment file
		if(!file_put_contents(self::$deployFile,$out))
			die(chr(27)."[91munable to write ".self::$deployFile.chr(27)."[0m\n");
		echo($chksum." ".strlen($out).chr(27)."[9".(strlen($out)>$old?"1":"4")."m (".(strlen($out)>$old?"+":"").(strlen($out)-$old).") ".chr(27)."[92mOK".chr(27)."[0m\n");
	}

/**
 * Update download and self test page in documentation
 */
    static function updateDoc()
    {
        if(file_exists(self::$docFile)) {
            echo(chr(27)."[96mUpdating documentation: ".chr(27)."[0m");
            //! get data
            $doc = file_get_contents(self::$docFile);
            $dep = file_get_contents(self::$deployFile);
            $tpl = file_get_contents(self::$selfTestFile);
            preg_match("|\'VERSION\',\'([^\']+)|",$dep,$ver);
            //! replace
            $doc = preg_replace(
                "|/\*core\*/\"data:text\/plain[^\"\']+|",
                "/*core*/\"data:text/plain;base64,".base64_encode($dep),
                preg_replace(
                "|/\*view\*/\"data:text\/plain[^\"\']+|",
                "/*view*/\"data:text/plain;base64,".base64_encode($tpl),
                    preg_replace(
                        "|<small data-ver>v[^<]+</small>|",
                        "<small data-ver>v".$ver[1]."</small>", $doc)));
            //! write out
            if(!file_put_contents(self::$docFile, $doc))
                die(chr(27)."[91munable to write ".self::$docFile.chr(27)."[0m\n");
            echo(chr(27)."[92mOK".chr(27)."[0m\n");
        }
    }

/**
 * Create extension tarballs and packages.json for PHP Composer
 * @usage php public/index.php mkrepo
 */
	static function make()
	{
		//! repository base url
		if(substr(Core::$core->action,0,4)=="http")
			self::$repoBase=Core::$core->action;
		if(substr(self::$repoBase,-1)!="/")
			self::$repoBase.="/";

        //! invalidate cache
        $files=glob(".tmp/.pkgs_*");
        foreach($files as $file) {
            @unlink($file);
        }
		//! if source changed, regenerate deployment file
		if(file_exists(self::$sourceFile) &&
			filemtime(self::$sourceFile) > filemtime(self::$deployFile)) {
			self::compress();
            self::updateDoc();
        }

		//! tar executable. On MacOSX this will avoid extra files in tarballs
		if(strtolower(trim(exec("uname -s")))=="darwin")
			$tar="COPYFILE_DISABLE=1 COPY_EXTENDED_ATTRIBUTES_DISABLE=1 tar --disable-copyfile";
		else
			$tar="tar";

		//! get packages
		echo(chr(27)."[96mScanning for packages:".chr(27)."[0m ");
		$jsons = glob("vendor/phppe/*/composer.json");
		echo(chr(27)."[92m".count($jsons).chr(27)."[0m found\n");
		$packages=[];

		//! for each composer.json, we do
		foreach($jsons as $json)
		{
			//! ***** tarball *****
			$dir=dirname($json);
			$ext=basename($dir);
			$bns=preg_match("/\"Business\"/",file_get_contents($json));
			$tarball="../../../".($bns?Core::$client->user:"phppe3")."_".strtolower($ext).".tgz";
			echo("  ".sprintf("%-20s",$ext.": "));
			//! create tarball if not exists or older than extension's files
			chdir($dir);
			if(!file_exists($tarball) || trim(exec("find . -cnewer ".$tarball." |grep -v /log/ 2>/dev/null"))!="")
			{
				//! copy sql.dists
				$sql=glob("sql/upd_*.dist");
				if(!empty($sql))
					foreach($sql as $s)
						copy($s,substr($s,0,strlen($s)-5));
				$files=implode(" ",array_diff(glob("*"),["composer.json","preview","log",$ext=="Extensions"?"config.php":""]));
				exec($tar." --exclude=*.dist -czf ".$tarball." composer.json ".$files."\n");
				//! remove sqls
				if(!empty($sql))
					foreach($sql as $s)
						unlink(substr($s,0,strlen($s)-5));
				echo(chr(27)."[92mtgz  ".chr(27)."[0m");
			} else
				echo(chr(27)."[90mskip ".chr(27)."[0m");
			//! read preview image
			if(file_exists("preview"))
				$preview=file_get_contents("preview");
			elseif(file_exists("images/phppe.png"))
				$preview=file_get_contents("images/phppe.png");
			else
				$preview="";
			chdir("../../..");

			//! ***** parse composer.json *****
			$m=json_decode(file_get_contents($json), true);
			//sanity check
			if(empty($m) || !is_array($m)||
				empty($m['name'])||substr($m['name'],0,6)!="phppe/"||
				empty($m['version'])||
				empty($m['license'])||
				(!empty($m['keywords'][0])&&!in_array($m['keywords'][0],self::$categories)))
			{
				echo(chr(27)."[91mbad json! ".chr(27)."[93m".json_last_error_msg().chr(27)."[0m\n");
				continue;
			}
			$packages[$m['name']]=$m;
			// hardwired priorities for the framework
			if($m['name']=="phppe/Core") $packages[$m['name']]['prio']=99999; else
			if($m['name']=="phppe/CMS") $packages[$m['name']]['prio']=99998; else
			if($m['name']=="phppe/Extensions") $packages[$m['name']]['prio']=99997; else
			if($m['name']=="phppe/Developer") $packages[$m['name']]['prio']=99996; else
			// validate fields
			$packages[$m['name']]['prio']=!empty($m["prio"])&&$m["prio"]>0&&$m["prio"]<99900?$m["prio"]+0:0;
			$packages[$m['name']]['dist']=['type'=>"tar",'url'=>($bns?self::$bnsBase:self::$repoBase).basename($tarball)];
			$packages[$m['name']]['description']=!empty($m["description_en"])?$m["description_en"]:(!empty($m["description"])?$m["description"]:"");
			$packages[$m['name']]['keywords']=!empty($m["keywords"])?$m["keywords"]:[];
			$packages[$m['name']]['maintainer']=!empty($m["maintainer"])?$m["maintainer"]:["name"=>"Anonymous"];
			$packages[$m['name']]['price']=!empty($m["price"])&&$m["price"]>0?intval($m["price"]):0;
			$packages[$m['name']]['time']=date("Y-m-d H:i:s",filemtime(basename($tarball)));
			$packages[$m['name']]['size']=filesize(basename($tarball));
			$packages[$m['name']]['sha1']=sha1(file_get_contents(basename($tarball)));
			if(!empty($preview))
				$packages[$m['name']]['preview']=base64_encode($preview);
            if($bns && empty($packages[$m['name']]['price']))
                $packages[$m['name']]['price']=1;

            echo(chr(27)."[92mOK".chr(27)."[0m\n");
		}

		//! sort packages
		usort($packages,function($a,$b){
			if(@$a['keywords'][0]!=@$b['keywords'][0]) return @$a['keywords'][0]>=@$b['keywords'][0];
			if(@$a['prio']+0!=@$b['prio']+0)return(@$a['prio']+0<@$b['prio']+0?1:-1);
			return ($a['name']<$b['name']?-1:1);
		});
		//! generate json
		$json="{\n\t\"packages\": {\n";$f=1;
		foreach($packages as $p=>$m){
			//failsafe
			if(empty($m['name'])||empty($m['version'])||(!empty($m['keywords'][0])&&$m['keywords'][0]=="Business")) continue;
			//name, version and details
		    $json.=($f?"":",\n")."\t\t\"".$m['name']."\": {\n\t\t  \"".$m['version']."\": {\n";$f=0;
		    self::dumppkg($p,$m,$json);
		    $json.="\n\t\t  }\n\t\t}";
		}
		$json.="\n\t}\n}\n";
		//! save packages info to packages.json
		echo(chr(27)."[96mSaving packages info:".chr(27)."[0m ");
		if(!file_put_contents("packages.json",$json))
			die("unable to write packages.json");
		$json="{\n\t\"packages\": {\n";$f=1;
		foreach($packages as $p=>$m){
			//failsafe
			if(empty($m['name'])||empty($m['version'])||empty($m['keywords'][0])||$m['keywords'][0]!="Business") continue;
			//name, version and details
		    $json.=($f?"":",\n")."\t\t\"".$m['name']."\": {\n\t\t  \"".$m['version']."\": {\n";$f=0;
		    self::dumppkg($p,$m,$json);
		    $json.="\n\t\t  }\n\t\t}";
		}
		$json.="\n\t}\n}\n";
		//! save packages info to packages.json
		if($f==0)
            file_put_contents("packages.business.json",$json);
        echo(chr(27)."[92mOK".chr(27)."[0m\n");
	}

/**
 * Reentrant json generator as json_encode() would report memory error...
 */
	private static function dumppkg($p,$m,&$json,$l=0)
	{
		$f=1;
		foreach($m as $k=>$v)
			if($k!="prio")
			{
				$json.=($f?"":",\n").str_repeat("\t",$l+3)."\"".str_replace("\\'","'",addslashes($k))."\":";
				$f=0;
				if(is_array($v)||is_object($v))
				{
					$json.=isset($v[0])?"[\"".$v[0]."\"":"{\n";
					self::dumppkg($k,$v,$json,$l+1);
					$json.="\n".str_repeat("\t",$l+3).(isset($v[0])?"]":"}");
				} else
					$json.="\"".str_replace("\\'","'",addslashes($v))."\"";
			}
	}
}
