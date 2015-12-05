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
<h2><!=L("Meta")></h2>
<b><!=L('URL')></b><nobr><!if quickhelp><i><!=url("/")></i><!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if><br/>
<b><!=L('Name')></b><!field *text page.name><!if quickhelp><small><br/><!L help_pagename></small><!/if><br/>
<b><!=L('Language')></b><!field select page.lang langs><br/>
<b><!=L('Layout')></b><!field select page.template layouts><br/>
<b><!=L('Meta data')></b><!field cmsmeta page.meta><!if quickhelp><small><br/><!L help_pagemeta></small><!/if><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


