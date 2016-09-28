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
 * @file vendor/phppe/Developer/libs/Templates.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Utility to create php files from templates
 */
namespace PHPPE;

class Templates
{
/**
 * Usage information
 * @usage php public/index.php create
 */
	static function getUsage()
	{
		echo(L("Usage").":\n  php public/index.php ".\PHPPE\Core::$core->app." <template> [arg1 [arg2 [...]]]\n\n".
			L("Template can be one of").":\n");

		foreach(self::readTemplates() as $name=>$meta)
			echo("  ".sprintf("%-20s",$name).(!empty($meta["desc_".\PHPPE\Core::$client->lang])?
				$meta["desc_".\PHPPE\Core::$client->lang]:
				$meta["desc_en"])."\n");
	}


/**
 * File creator. See available templates in vendir/phppe/Developer/templates
 * @usage php public/index.php create <template> [arg1 [arg2...]]
 *
 * @param template to generate file with
 */
	static function create($template)
	{
		//! get meta data for template
		$meta = self::getMeta($template);

		//! get variables from arguments
		$vars = [];
		if(!empty($meta['args']))
		{
			foreach($meta['args'] as $k=>$a)
			{
				if(empty($_SERVER['argv'][$k+3]))
					die(L("Usage").
						":\n  php public/index.php ".\PHPPE\Core::$core->app." ".
						$template." <".implode("> <",$meta['args']).">\n");
				self::loadVars($vars,$a,$_SERVER['argv'][$k+3]);
			}
		}
		//! some more variables you can use in templates
		$vars['@@USERNAME'] = \PHPPE\Core::$client->user;
		$vars['@@RFCDATE'] = date("r");
		$vars['@@ISODATE'] = date("c");
		$vars['@@DATETIME'] = date("YmdHis");
		$vars['@@TIMESTAMP'] = time();

		//! get more templates for package
		if(!empty($meta['package']))
			foreach($meta['package'] as $p=>$d)
			{
				$metas[$p]=self::getMeta($p);
			}
		$metas[$template]=$meta;
		//! create directories (only for top template)
		if(!empty($meta['dirs']))
			foreach($meta['dirs'] as $dir)
				mkdir(strtr($dir,$vars),0750,true);

		//! for each template generate file
		foreach($metas as $tpl=>$m)
		{
			//! get default variables
			if(!empty($meta['package'][$tpl]))
				foreach($meta['package'][$tpl] as $k=>$v)
					self::loadVars($vars,$k,!empty($vars[$v])?$vars[$v]:strtr($v,$vars));
			//! get name of the file
			$file = strtr(!empty($m['file'])?$m['file']:$m['append'],$vars);
	
			//! add license and brief strings
			$vars["@@LICENSE"]="/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL ".date("Y")."
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
 * @file ".$file."
 * @author ".\PHPPE\Core::$client->user."
 * @date ".date("d M Y")."
 * @brief
 */";
			$vars["@@BRIEF"]="/**
 * @file ".$file."
 * @author ".\PHPPE\Core::$client->user."
 * @date ".date("d M Y")."
 * @brief
 */";

			//! create directory, it may exists already
			$dir = dirname($file);
			if(!empty($dir))
				@mkdir($dir,0750,true);
	
			//! save the new file or append
			if(!file_put_contents(
				$file,
				strtr($m['data'],$vars),
				!empty($m['append'])?FILE_APPEND:null)
			)
				die(L("Unable to write").": ".$file."\n");
			echo(L("Writing")." ".$file."\n");
		}
	}

/**
 * Read templates
 */
	static function readTemplates($template="")
	{
		//! get list of templates to read
		if(empty($template))
			$files = glob(__DIR__."/../templates/*");
		else
			$files = [ __DIR__."/../templates/".$template ];
		sort($files);

		//! read and parse template files
		$templates = [];
		foreach($files as $f)
		{
			$data = file_get_contents($f);
			if(empty($data))
				die(L("Unable to read").": vendor/phppe/Developer/templates/".basename($f)."\n");
			$i = strpos($data,"\n}\n");
			if(empty($data) || $i===false)
				continue;
			$meta = json_decode(substr($data,0,$i+3), true);
			if(is_array($meta))
				$meta['data'] = substr($data,$i+3);

			$templates[basename($f)]=$meta;
		}

		//! return what we have got
		return $templates;
	}

	static function getMeta($template)
	{
		$meta = @self::readTemplates($template)[$template];
		if(empty($meta) || is_string($meta) ||
			(empty($meta['file']) && empty($meta['append']) && empty($meta['package'])))
			die(L("Unable to read meta")." (".$template.")\n".(is_string($meta)?$meta."\n":"").json_last_error_msg());

		return $meta;
	}

	static function loadVars(&$vars,$name,$value)
	{
		$vars["@".strtoupper($name)."@"] = $value;
		$vars["@".strtolower($name)."@"] = strtolower($value);
		$vars["@".ucfirst($name)."@"] = ucfirst($value);
	}
}
