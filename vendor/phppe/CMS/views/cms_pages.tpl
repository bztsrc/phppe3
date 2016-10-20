<h1><!=L("Pages")></h1>
<div style='padding:5px;'>
<!if !ispublish>
<button onclick='pe.cms.edit(this,"f95f1f92080432dc901529d428fd32fa12d7b948",0,0,0,0,0,80);' class='btn btn-success'><span class='glyphicon glyphicon-plus-sign'></span>&nbsp;<!=L("pageadd")></button>&nbsp;
<!if needpublish>
<button onclick='document.location.href="cms/pages?publish";' class='btn btn-warning'><span class='glyphicon glyphicon-cloud-upload'></span>&nbsp;<!=L("Publish")></button>
<!/if>
<button onclick='document.location.href="cms/pages?order=1";' class='btn'><!=L("By layout")></button>
<button onclick='document.location.href="cms/pages?order=0";' class='btn'><!=L("Recent")></button>
<!else>
<!form publish>
<input type='hidden' name='publish' value='1'>
<button onclick='event.preventDefault();document.location.href="cms/pages";' class='btn'><span class='glyphicon glyphicon-arrow-left'></span>&nbsp;<!=L("Back")></button>
<button class='btn btn-danger'><span class='glyphicon glyphicon-check'></span>&nbsp;<!=L("Publish")></button>
<!/if>
<nobr><!field select pagelang langs - pe.cms.tablesearch(this.nextSibling,"results")>
<input id='search' class='input form-control' type='text' style='display:inline;width:50%;' onkeyup='return pe.cms.tablesearch(this,"results");' placeholder='<!=L('Search')>'><span style='font-size:20px;padding-left:5px;padding-right:5px;'>⌕</span></nobr>
</div>
<table id="results" class="cmstable resptable">
	<tr>
		<th><!if ispublish><input type='checkbox' onchange='pe.cms.checkall(this);' checked><!else><!=L("Layout")><!/if></th>
		<th></th>
		<th>URL</th>
		<th width='50%'><!=L("Title")></th>
		<th><!=L("Filters")></th>
		<th><nobr><!=L("Modified by")></nobr></th>
		<th><nobr><!=L("Modified at")></nobr></th>
		<th><!=L("#versions")></th>
		<th><!=L("Locked")></th>
	</tr>
<!foreach pages>
<!if strtolower(KEY)!='frame'>
<!if KEY>
<tr><td colspan='10' style='font-weight:bold;' data-skipsearch='1'><!=L(KEY)></td></tr>
<!/if>
<!foreach VALUE>
<!if strtolower(id)!='frame'>
	<tr style='cursor:pointer;'<!if publishid==0> class='unpublished'<!/if> <!if !app.ispublish>onclick='document.location.href="<!=url(id=='index'?'/':id)><!if !empty(lang)>?lang=<!=lang><!/if>";'<!/if> data-lang='<!=lang>'>
<!if app.ispublish>
		<td data-skipsearch=1><input type='checkbox' id='publish_<!=id>' name='publish_<!=id>' checked></td>
<!else>
<!if parent.KEY>
		<td style='color:#808080;font-size:10px;'><!=tid></td>
<!else>
		<td style='color:#808080;font-size:10px;'><!if L(tid)!=tid||empty(template)><!=ucfirst(L(tid))><!else><!=L(template)><!/if></td>
<!/if>
<!/if>
		<td<!if ownerid> style='color:#800000;'<!/if> width='25'><img src='images/lang_<!=lang>.png' alt='<!=lang>' title='<!=lang>'></td>
		<td<!if ownerid> style='color:#800000;'<!/if> dir='ltr'><!if id=='index'><span class='glyphicon glyphicon-home'></span> /<!else><!=id><!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><a style='color:inherit;text-decoration;none;' href='<!=url(id=='index'?'/':id)><!if !empty(lang)>?lang=<!=lang><!/if>'><!=name></a></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!=filter></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!if moduser><!=moduser><!else><!if modifyid==-1>admin<!else>?<!/if><!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if> dir='ltr' align='right'><nobr><!if !modifyd><!difftime strtotime(created)-core.now><!else><!difftime strtotime(modifyd)-core.now><!/if></nobr></td>
		<td<!if ownerid> style='color:#800000;'<!/if> align='right'><!=versions></td>
		<td align='right' dir='ltr' data-skipsearch='1' align='right'><nobr><!if ownerid><a href='<!=url('cms','unlock')><!=id>' style='text-decoration:none;color:<!if ownerid>#800000<!else>#000<!/if>;' title='<!=L("Unlock page")>'><!if ownerid==-1>admin<!else><!=lockuser><!/if> <small>(<!difftime strtotime(lockd)-core.now>)</small>&nbsp;<img src='images/cms/unlock.png' alt='' align='abscenter'></a><!/if></nobr></td>
	</tr>
<!/if>
<!/foreach>
<!/if>
<!/foreach>
</table>
</form>
<br>
<!-- L("Index") L("Simple") -->