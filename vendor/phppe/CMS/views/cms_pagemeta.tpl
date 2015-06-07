<style>
.input {
	color:#000;
}
B {
	display:block;
	margin-top:8px;
}
</style>
<div class='confpanel'>
<!form page>
<h2><!=L("Page Meta")></h2>
<b><!=L('URL')></b><nobr><!if quickhelp><i><!=url("/")></i><!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if><br/>
<b><!=L('Name')></b><!field *text page.name><!if quickhelp><small><br/>This will be set as browser's window title</small><!/if><br/>
<b><!=L('Language')></b><!field select page.lang langs><br/>
<b><!=L('Layout')></b><!field select page.template layouts><br/>
<b><!=L('Meta Keywords')></b><!field cmsmeta page.meta><!if quickhelp><small><br/>Each line one <i>key=value</i> pair</small><!/if><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


