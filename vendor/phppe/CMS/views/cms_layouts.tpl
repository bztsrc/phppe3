<!if empty(core.item)>
<h1><!L CMS Layouts></h1>
<table>
	<tr>
		<th>Name</th>
		<th>CSS</th>
		<th>JS Library</th>
		<th>Meta data</th>
		<th>Modified at</th>
	</tr>
<!foreach layouts>
	<tr style='cursor:pointer;' onclick='document.location.href="<!=url()><!=id>";'>
		<td class='row<!=ODD>'><!=id></td>
		<td class='row<!=ODD>'><!if css>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if jslib>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!if meta>&radic;<!else>&Oslash;<!/if></td>
		<td class='row<!=ODD>'><!time created></td>
	</tr>
<!/foreach>
</table>
<!else>
<!form layout>
<fieldset>
	<legend><!L Layout></legend>
<nobr><!L ID>: <!field text layout.id></nobr>&nbsp;&nbsp;&nbsp;
<nobr><!L Css>: <!field cmscss layout.css></b></nobr><!if quickhelp><small>(<!L each line one realitve or absolute url to a stylesheet>)</small><!/if>&nbsp;&nbsp;&nbsp;
<nobr><!L JS>: <b><!field cmsjs layout.jslib></b></nobr><!if quickhelp><small>(<!L each line one realitve or absolute url to a javascript library>)</small><!/if>&nbsp;&nbsp;&nbsp;
<nobr><!L Meta>: <b><!field cmsmeta layout.meta></b></nobr><!if quickhelp><small>(<!L each line one key=value pairs>)</small><!/if>&nbsp;&nbsp;&nbsp;
<!field update Save><!if quickhelp><small>(<!L also saves editor area>)</small><!/if><br/>
</fieldset>
<div id='layout_edit'><!field wysiwyg('cms_layout') layout.data><!if quickhelp><small>(<!L note the changing toolbar as you select items>)</small><!/if></div>
<!=L('Import sitebuild html')>: <!field file layout.input><!if quickhelp><small>(<!L upload index.html (or any other html) from your sitebuild>)</small><!/if>
</form>
<!/if>
