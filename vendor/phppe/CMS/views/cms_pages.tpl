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
	</tr>
<!foreach _pages>
<tr><td colspan='10'><b><!=L(KEY)></b></td></tr>
<!foreach VALUE>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=urlencode(id)>";'>
		<td></td>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>'><!=name></td>
		<td class='row<!=ODD>'><!=filter></td>
		<td class='row<!=ODD>'><!if dds>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if username><!=username><!else><!if ownerid==-1>admin<!else>UNKNOWN<!/if><!/if></td>
		<td class='row<!=ODD>'><!time created></td>
		<td class='row<!=ODD>' align='right'><!=versions></td>
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
