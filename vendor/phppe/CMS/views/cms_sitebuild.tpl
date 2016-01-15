<!if choose>
<h1><!=L("Sitebuilds")></h1>
<!=L("There are more HTML files in the archive. Choose one!")>
<table class="cmstable">
	<tr>
		<th><!=L("Layout")></th>
		<th><!=L("Title")></th>
	</tr>
<!foreach html>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()>?cms_sitebuild=<!=KEY>";'>
		<td class='row<!=ODD>'><!=basename(VALUE)></td>
		<td class='row<!=ODD>'><!=title[KEY]></td>
	</tr>
<!/foreach>
</table>
<!else>
<b style='margin:3px;display:block;'><!=L('Select application area')></b>
<div style='border:inset 1px rgba(128,128,128,0.5);border-radius: 5px;box-shadow: 0 -1px 2px #999;padding:0px 0px 10px 0px;'>
<div id='divchoose' data-chooseid='0' onmousemove='cms_divchoosemove(event);' onclick='cms_divchooseclick(event);'><br><!=content></div>
</div>
<img id='loading' src='images/loading.gif' style='display:none;'>
<!if quickhelp>
<small>(<!=L("help_saimportdiv")>)</small>
<!/if>
<!/if>
