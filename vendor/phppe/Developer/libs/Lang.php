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
		echo(L("Usage").":\n  php public/index.php ".\PHPPE\Core::$core->app." <Extension> [language]\n\n".
			L("If language not given, detects strings in code, otherwise merges language array."));
	}


/**
 * Parse php code for translatable strings and merge with language dictionary
 * @usage php public/index.php lang <extension> [languagecode]
 *
 * @param extension name
 * @param two letter language code
 */
	static function parse($extension, $lang="")
	{
	}
}
