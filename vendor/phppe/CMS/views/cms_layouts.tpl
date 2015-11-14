<!if empty(core.item)>
<h1><!L Layouts></h1>
<table>
	<tr>
		<th><!=L('ID')></th>
		<th><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><!=L('Meta Keywords')></th>
		<th><!=L('Modified at')></th>
	</tr>
<!foreach layouts>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!difftime modifyd ct></td>
	</tr>
<!/foreach>
</table>
<h1><!L Sitebuilds></h1>
<table>
	<tr>
		<th></th>
		<th><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><!=L('Meta Keywords')></th>
		<th><!=L('Modified at')></th>
	</tr>
<!foreach sitebuilds>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>'><!if id=='frame'>&radic;<!else><a href='<!=url()>?set=<!=urlencode(name)>' style='text-decoration:none;color:#000;'>&Oslash;</a><!/if></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!difftime modifyd ct></td>
	</tr>
<!/foreach>
</table>
<!if quickhelp>
<small>(<!L help_sitebuilds>)</small>
<!/if>
<br/><br/>
<!form import>
<!=L("Import sitebuild zip")>: <!field file import.file><!field submit>
</form>
<!if quickhelp>
<small>(<!=L("help_sbimport")>)</small>
<!/if>
<!else>
<!form layout>
<fieldset>
	<legend><!L Layout></legend>
<nobr><!L ID>: <!field text layout.id>&nbsp;&nbsp;<!L Name>: <!field text layout.name></nobr><br/>
<nobr><!L CSS>: <!field cmscss layout.css></b></nobr><!if quickhelp><small>(<!L help_css>)</small><!/if><br/>
<nobr><!L JS>: <b><!field cmsjs layout.jslib></b></nobr><!if quickhelp><small>(<!L help_js>)</small><!/if><br/>
<nobr><!L Meta Keywords>: <b><!field cmsmeta layout.meta></b></nobr><!if quickhelp><small>(<!L help_pagemeta>)</small><!/if><br/>
<!=L('Import sitebuild html')>: <!field file layout.input><!if quickhelp><small>(<!L help_importhtml>)</small><!/if><br/>
<!field update Save><!if quickhelp><small>(<!L help_layoutsave>)</small><!/if><br/>
</fieldset>
<div id='layout_edit'><!field wysiwyg('cms_layout') layout.data><!if quickhelp><small>(<!L help_toolbar>)</small><!/if></div>
</form>
<!/if>
