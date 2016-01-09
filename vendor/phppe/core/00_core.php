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
 * @file vendor/phppe/core/00_core.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief PHPPE Core Extensions
 */
namespace PHPPE {
	//! regardless to autoloaders, make sure the Content class exists
	include_once("vendor/phppe/core/libs/content.php");
	//! register ourself, using Content as object reference
	//! if we would use PHPPE::$core, that would end in an
	//! infinite reference loop. So we have to use a different object
	Core::lib("Core","PHPPE Core","",new Content);

	//! register built-in fields and widets all at once
	//! this is required for \PHPPE\Core::isinst() to always return true for built-ins
	Core::addon( "hidden", "Hidden value", "", "*obj.field" );
	Core::addon( "button", "Button", "", "*label onclickjs [cssclass]" );
	Core::addon( "update", "Update", "", "*[label [onclickjs [cssclass]]]" );
	Core::addon( "cancel", "Cancel", "", "*[label [cssclass]]" );
	Core::addon( "text", "Text", "", "*(size[,maxlen[,rows]]) obj.field [onchangejs [cssclass [onkeyupjs [fakevalue]]]]" );
	Core::addon( "pass", "Password", "", "*(size[,maxlen]) obj.field [onchangejs [cssclass]]" );
	Core::addon( "num", "Decimal number", "", "*(size[,maxlen[,min[,max]]]) obj.field [onchangejs [cssclass]" );
	Core::addon( "select", "Option list", "", "*(size[,ismultiple]) obj.field options [skipids [onchangejs [cssclass]]]" );
	Core::addon( "check", "Checkbox", "", "*(truevalue) obj.field [label [cssclass]]" );
	Core::addon( "radio", "Radiobutton", "", "*(value) obj.field [label [cssclass]]" );
	Core::addon( "phone", "Phone number", "", "*(size[,maxlen]) obj.field [onchangejs [cssclass]]" );
	Core::addon( "email", "Email address", "", "*(size[,maxlen]) obj.field [onchangejs [cssclass]]" );
	Core::addon( "file", "File", "", "*(size[,maxlen]) obj.field [cssclass]" );
	Core::addon( "date", "Date", "", "*(before[,after]) obj.field [cssclass]" );
	Core::addon( "time", "Time", "", "*(before[,after]) obj.field [cssclass]" );

	//! normalize stylesheets and standardize javascript
	//! also generate Table of Contents (we do it here on purpose)
	Core::css("normalize.css");
	Core::jslib("jquery.js","toc_init();");
}
namespace PHPPE\AddOn {
	//! other useful, js only fields and widgets extensions shipped with Pack
	class toc extends \PHPPE\AddOn { function init(){\PHPPE\Core::addon("toc","Table of Contents");} }
	class popup extends \PHPPE\AddOn { function init(){\PHPPE\Core::addon("popup","Popup titles and menus");} }
	class zoom extends \PHPPE\AddOn { function init(){\PHPPE\Core::addon("zoom","Zooming pictures and divs"); \PHPPE\Core::js("init()","zoom_init();",true);} }
	class dnd extends \PHPPE\AddOn { function init(){\PHPPE\Core::addon("dnd","Drag'n'Drop"); \PHPPE\Core::js("init()","dnd_init();",true);} }
	class resptable extends \PHPPE\AddOn { function init(){\PHPPE\Core::addon("resptable","Responsive tables"); \PHPPE\Core::jslib("resptable.js","resptable_init();");} }
}
?>