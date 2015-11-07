<!if empty(core.item)>
<h1><!L Pages></h1>
<table>
	<tr>
		<th><!L Layout></th>
		<th>URL</th>
		<th><!L Title></th>
		<th><!L Filters></th>
		<th><!L DDS></th>
		<th><!L Modified by></th>
		<th><!L Modified at></th>
		<th><!L #versions></th>
		<th><!L Locked></th>
	</tr>
<!foreach _pages>
<tr><td colspan='10'><b><!=L(KEY)></b></td></tr>
<!foreach VALUE>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td></td>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!=filter></td>
		<td class='row<!=ODD>'><!if dds>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if username><!=username><!else><!if ownerid==-1>admin<!else>UNKNOWN<!/if><!/if></td>
		<td class='row<!=ODD>'><!if !modifyd><!difftime created ct><!else><!difftime modifyd ct><!/if></td>
		<td class='row<!=ODD>' align='right'><!=versions></td>
		<td class='row<!=ODD>' align='right'><a href='<!=url()>?unlock=<!=urlencode(id)>' style='text-decoration:none;color:#000;'><!difftime lockd ct></a></td>
	</tr>
<!/foreach>
<!/foreach>
</table>
<!else>
<!if !empty(core.nopanel) || !user.has('panel')>
<div id='cmspanel'>
<!include cms_pagepanel>
</div>
<!/if>
<!=app._result>
<!/if>
