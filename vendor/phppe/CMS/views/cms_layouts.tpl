<!if empty(core.item)>
<h1><!=L("Layouts")></h1>
<div style='padding:5px;'>
<!form import>
<span style='display:none;'>
<!field file import.file - this.form.submit()></span>
</form>
<button onclick='document.getElementById("import_file").click();' class='btn'><span class='glyphicon glyphicon-open'></span>&nbsp;<!=L("Import sitebuild zip")></button>
<button onclick='pe.cms.edit(this,"45233837fc8c595272246ef3d48f311c842ce562",0,0,0,400,160,30);' class='btn'><span class='glyphicon glyphicon-plus-sign'></span>&nbsp;<!=L("layoutadd")></button>
<nobr><input id='search' class='input form-control' type='text' style='display:inline;width:50%;' onkeyup='return pe.cms.tablesearch(this,"results");' placeholder='<!=L('Search')>'><span style='font-size:20px;padding-left:5px;padding-right:5px;'>⌕</span></nobr>
</div>
<table id="results" class="cmstable">
	<tr>
		<th width='1'><!=L('ID')></th>
		<th width='50%'><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><nobr><!=L('Meta data')></nobr></th>
		<th><nobr><!=L('Modified at')></nobr></th>
	</tr>
<!foreach layouts>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>' width='100%'><!=name></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>' dir='ltr'><nobr><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></nobr></td>
	</tr>
<!/foreach>
	<tr>
		<th><!=L("Sitebuilds")></th>
		<th><!=L('Name')></th>
		<th><!=L('CSS')></th>
		<th><!=L('JS')></th>
		<th><!=L('Meta data')></th>
		<th><!=L('Modified at')></th>
	</tr>
<!foreach sitebuilds>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>' align='center' dir='ltr'><!if id=='frame'><span style='font-weight:bold;color:green;height:14px;padding-top:3px;'>&radic;</span><!else><a class='button' href='<!=url()>?set=<!=urlencode(sitebuild)>' style='height:18px;line-height:16px;padding-top:2px;text-decoration:none;color:#000;'>&Oslash;</a><!/if></td>
		<td class='row<!=ODD>'><!=L(name)></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>' dir='ltr'><nobr><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></nobr></td>
	</tr>
<!/foreach>
</table>
<!if quickhelp>
<small>(<!L help_sitebuilds>)</small><br/>
<small>(<!=L("help_sbimport")>)</small>
<!/if>
<br/><br/>
<!include errorbox>
<!else>
<!if core.noframe>
<div style='height:24px;'>&nbsp;</div>
<!/if>
<div id='layoutdiv'>
<!form layout>
<input type='hidden' id='layout_delete' name='layout_delete' value='0'>
<div id='layout_fieldset'>
<div style='float:left;width:200px;'>
<!=L("ID")>, <!L Name>:<br/><!field text layout.id>
<!field text layout.name>
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
<div id='layout_edit'><!field wyswyg(0,'pe.cms.image','{"tag":"cms/tag"}') layout.data><!if quickhelp><small>(<!L help_toolbar>)</small><!/if></div>
<div class='toolbar'><!field update Delete pe.cms.layoutdel(event)><!field button Info pe.cms.fieldset(event)><!field update Save></div>
</form>
<!/if>
<input type='hidden' id='layout_numPages' value='<!=numPages>'>
<script>
// make sure the editor is not below the toolbar
setTimeout(function(){
var e=document.getElementById('layout_edit');
var p=e.getBoundingClientRect();
if(p.top<64) e.style.marginTop='24px';
},1);
</script>
