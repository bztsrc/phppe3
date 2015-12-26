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
<div id='layoutdiv'>
<!form layout>
<div id='layout_fieldset' class='blockform' style='display:none;' data-zoom-nodecor='1'><div class='confpanel'>
<h2><!=L("Layout Info")></h2>
<!if copyout><div><!=L("Imported")>:<br/><pre><!=copyout></pre></div><!/if>

<div style='float:left;width:200px;'>
<!L ID>:<br/><!field text layout.id><br/><br/>
<!L Name>:<br/><!field text layout.name>
</div>
<div style='float:left;width:400px;'>
<!L Meta data>: <!field cmsmeta layout.meta>
<!if quickhelp><small>(<!L help_pagemeta>)</small><!/if>
</div>
<div style='float:left;'>
<!L Style sheets>:<br/><!field cmscss layout.css>
<!if quickhelp><br/><small>(<!L help_css>)</small><!/if>
</div>
<div style='float:left;'>
<!L JavaScript libraries>:<br/><!field cmsjs layout.jslib>
<!if quickhelp><br/><small>(<!L help_js>)</small><!/if>
</div>
</div>
</div>
<div style='text-align:right;padding:5px;margin-bottom:-24px;'><!field update Save></div>
<div id='layout_edit'><!field wysiwyg('cms_layout') layout.data><!if quickhelp><small>(<!L help_toolbar>)</small><!/if></div>
<div style='text-align:right;padding:5px;margin-top:-16px;'><!field update Save></div>
</form>
<!/if>
