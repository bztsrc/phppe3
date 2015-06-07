<!if empty(core.item)>
<h1><!L CMS Pages></h1>
<table>
	<tr>
		<th>Layout</th>
		<th>URL</th>
		<th>Title</th>
		<th>Filters</th>
		<th>DDS</th>
		<th>Modified by</th>
		<th>Modified at</th>
		<th>#versions</th>
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
		<td class='row<!=ODD>'><!time created></td>
		<td class='row<!=ODD>' align='right'><!=versions></td>
	</tr>
<!/foreach>
<!/foreach>
</table>
<!else>
<!if !user.has('panel')>
<div id='cmspanel'>
<!include cms_pagepanel>
</div>
<!/if>
<!=app._result>
<!/if>
