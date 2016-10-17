<h1><!=L("Pages")></h1>
<div style='padding:5px;'>
<button onclick='pe.cms.edit(this,"f95f1f92080432dc901529d428fd32fa12d7b948",0,0,0,0,0,80);' class='btn'><span class='glyphicon glyphicon-plus-sign'></span>&nbsp;<!=L("pageadd")></button>
<button onclick='document.location.href="cms/pages?order=1";' class='btn'><!=L("By layout")></button>
<button onclick='document.location.href="cms/pages?order=0";' class='btn'><!=L("Recent")></button>
<nobr><input id='search' class='input form-control' type='text' style='display:inline;width:50%;' onkeyup='return pe.cms.tablesearch(this,"results");' placeholder='<!=L('Search')>'><span style='font-size:20px;padding-left:5px;padding-right:5px;'>⌕</span></nobr>
</div>
<table id="results" class="cmstable resptable">
	<tr>
		<th><!=L("Layout")></th>
		<th></th>
		<th>URL</th>
		<th width='100%'><!=L("Title")></th>
		<th><!=L("Filters")></th>
		<th><!=L("DDS")></th>
		<th><!=L("Modified by")></th>
		<th><!=L("Modified at")></th>
		<th><!=L("#versions")></th>
		<th><!=L("Locked")></th>
	</tr>
<!foreach pages>
<!if strtolower(KEY)!='frame'>
<!if KEY>
<tr><td colspan='10' style='font-weight:bold;' data-skipsearch='1'><!=L(KEY)></td></tr>
<!/if>
<!foreach VALUE>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url(id=='index'?'/':id)><!if !empty(lang)>?lang=<!=lang><!/if>";'>
<!if parent.KEY>
		<td style='color:#808080;font-size:10px;'><!=tid></td>
<!else>
		<td style='color:#808080;font-size:10px;'><!if L(tid)!=tid||empty(template)><!=ucfirst(L(tid))><!else><!=L(template)><!/if></td>
<!/if>
		<td<!if ownerid> style='color:#800000;'<!/if> width='25'><img src='images/lang_<!=lang>.png' alt='<!=lang>' title='<!=lang>'></td>
		<td<!if ownerid> style='color:#800000;'<!/if> dir='ltr'><!if id=='index'><span class='glyphicon glyphicon-home'></span> /<!else><!=id><!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!=name></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!=filter></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!if !empty(dds) && dds!='[]'>&radic;<!else>&Oslash;<!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if>><!if moduser><!=moduser><!else><!if modifyid==-1>admin<!else>?<!/if><!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if> dir='ltr'><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></td>
		<td<!if ownerid> style='color:#800000;'<!/if> align='right'><!=versions></td>
		<td align='right' dir='ltr' data-skipsearch='1'><!if ownerid><a href='<!=url('cms','unlock')><!=id>' style='text-decoration:none;color:<!if ownerid>#800000<!else>#000<!/if>;' title='<!=L("Unlock")>'><!if ownerid==-1>admin<!else><!=lockuser><!/if> <small>(<!difftime strtotime(lockd)-strtotime(ct)>)</small>&nbsp;<img src='images/cms/unlock.png' alt='' align='abscenter'></a><!/if></td>
	</tr>
<!/foreach>
<!/if>
<!/foreach>
</table>
<br>
<!-- L("Index") L("Simple") -->