<!if empty(core.item)>
<div class='mosaicbox'><h1><!=L("Pages")></h1>
<span style='display:block;padding:5px;'>
<button onclick='document.location.href="cms/pages?order=0";'><!=L("By layout")></button>
<button onclick='document.location.href="cms/pages?order=1";'><!=L("Recent")></button>
<nobr><input id='search' type='text' style='width:30%;height:20px;' onkeyup='return cms_tablesearch(this,"results");'><input type='button' class='button' style='font-size:28px;padding-top:0px;line-height:24px;vertical-align:middle;width:8%;' onclick='cms_tablesearch(this.previousSibling,"results");' value='⌕'></nobr>
</span>
<table id="results" class="cmstable">
	<tr>
		<th><!=L("Layout></th>
		<th>URL</th>
		<th><!=L("Title")></th>
		<th><!=L("Filters")></th>
		<th><!=L("DDS")></th>
		<th><!=L("Modified by")></th>
		<th><!=L("Modified at")></th>
		<th><!=L("#versions")></th>
		<th><!=L("Locked")></th>
	</tr>
<!foreach _pages>
<!if KEY>
<tr><td colspan='10' style='font-weight:bold;' data-skipsearch='1'><!=KEY></td></tr>
<!/if>
<!foreach VALUE>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
<!if parent.KEY>
		<td style='color:#808080;font-size:10px;'><!=tid></td>
<!else>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if>><!=L(template)></td>
<!/if>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if> dir='ltr'><!=id></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if>><!=name></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if>><!=filter></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if>><!if dds>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if>><!if moduser><!=moduser><!else><!if modifyid==-1>admin<!else>?<!/if><!/if></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if> dir='ltr'><!if !modifyd><!difftime strtotime(created)-strtotime(ct)><!else><!difftime strtotime(modifyd)-strtotime(ct)><!/if></td>
		<td class='row<!=ODD>'<!if ownerid> style='color:#800000;'<!/if> align='right'><!=versions></td>
		<td class='row<!=ODD>' align='right' dir='ltr' data-skipsearch='1'><!if lockd><a href='<!=url()>?unlock=<!=urlencode(id)>' style='text-decoration:none;color:<!if ownerid>#800000<!else>#000<!/if>;' title='<!=L("Unlock")>'><img src='images/cms/unlock.png' alt='' align='abscenter'>&nbsp;<!if ownerid==-1>admin<!else><!=lockuser><!/if> <small>(<!difftime strtotime(lockd)-strtotime(ct)>)</small></a><!/if></td>
	</tr>
<!/foreach>
<!/foreach>
</table>
</div>
<!else>
<!if !empty(core.nopanel) || !user.has('panel')>
<div id='cmspanel'>
<!include cms_pagepanel>
</div>
<!/if>
<!=app._result>
<!/if>
