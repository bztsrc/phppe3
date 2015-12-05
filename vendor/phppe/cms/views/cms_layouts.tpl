<!if empty(core.item)>
<div class='mosaicbox'><h1><!L Layouts></h1>
<table>
	<tr>
		<th><!=L('ID')></th>
		<th><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><!=L('Meta data')></th>
		<th><!=L('Modified at')></th>
	</tr>
<!foreach layouts>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></td>
	</tr>
<!/foreach>
</table>
</div>
<div class='mosaicbox'><h1><!L Sitebuilds></h1>
<table>
	<tr>
		<td></td>
		<th><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><!=L('Meta data')></th>
		<th><!=L('Modified at')></th>
	</tr>
<!foreach sitebuilds>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>' align='center'><!if id=='frame'><span style='font-weight:bold;color:green;height:14px;padding-top:3px;'>&radic;</span><!else><a class='button' href='<!=url()>?set=<!=urlencode(sitebuild)>' style='height:8px;padding-top:2px;text-decoration:none;color:#000;'>&Oslash;</a><!/if></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></td>
	</tr>
<!/foreach>
</table>
<!if quickhelp>
<small>(<!L help_sitebuilds>)</small>
<!/if>
<br/><br/>
<!form import>
<!=L("Import sitebuild zip")>: <!field file import.file - this.style.display="none";this.form.submit()>
</form>
<!if quickhelp>
<small>(<!=L("help_sbimport")>)</small>
<!/if>
</div>
<!else>
<!if choose>
<div id='divchoose'>
<b style='margin:3px;display:block;'><!=L('Select area to import')></b>
<div style='border:inset 1px rgba(128,128,128,0.5);border-radius: 5px;box-shadow: 0 -1px 2px #999;padding:0px 0px 10px 0px;'>
<div data-chooseid='0' onmousemove='cms_divchoosemove(event);' onclick='cms_divchooseselect(event);'><br><!=choose></div></div>
<!if quickhelp>
<small>(<!=L("help_sbimportdiv")>)</small>
<!/if>
</div>
<!/if>
<div id='layoutdiv'<!if choose> style='display:none;'<!/if>>
<!form layout>
<div id='layout_fieldset' style='display:table-cell;'>
<!widget popup>
<div style='display:block;min-height:16px;float:none;'>
<span<!if copyout> onmouseover='popup_open(this,"copyout",10,10);'<!/if>><!=L('Import sitebuild html')>: <!field file layout.input - this.style.display="none";this.form.submit()></span><div id='copyout' style='box-shadow: 3px 3px 8px #000;position:absolute;background:#A0A0A0;padding:5px;z-index:10;display:none;'><!if copyout><pre><!=L("Imported")>:<br><!=copyout></pre><!/if></div>
<!if quickhelp><small>(<!L help_importhtml>)</small><!/if>
</div>
<div style='text-align:right;'>
<nobr><!L ID>: <!field text layout.id></nobr><br/>
<nobr><!L Name>: <!field text layout.name></nobr>
</div>
<div>
<!L Meta data>: <!field cmsmeta layout.meta>
<!if quickhelp><small>(<!L help_pagemeta>)</small><!/if>
</div>
<div>
<!L Style sheets>:<br/><!field cmscss layout.css>
<!if quickhelp><br/><small>(<!L help_css>)</small><!/if>
</div>
<div>
<!L JavaScript libraries>:<br/><!field cmsjs layout.jslib>
<!if quickhelp><br/><small>(<!L help_js>)</small><!/if>
</div>
</div>
<br style='clear:both;'>
<div style='text-align:right;padding:5px;margin-bottom:-30px;'><!field update Save></div>
<div id='layout_edit'><!field wysiwyg('cms_layout') layout.data><!if quickhelp><small>(<!L help_toolbar>)</small><!/if></div>
<div style='text-align:right;padding:5px;'><!field update Save></div>
</form>
<!/if>
