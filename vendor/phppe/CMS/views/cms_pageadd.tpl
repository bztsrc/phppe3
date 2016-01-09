<style>
B {
	display:block;
	margin-top:8px;
}
</style>
<div class='confpanel blockform'>
<!form page>
<h2><!=L(page.id?"Clone_page":"Add new page")></h2>
<b><!=L('URL')></b><nobr><!if quickhelp><i><!=url("/")></i>&nbsp;<!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if><br/>
<div><b><!=L('Name')></b><!field *text page.name><!if quickhelp><small><br/><!L help_pagename></small><!/if></div>
<div><b><!=L('Language')></b><!field select page.lang langs></div>
<div><b><!=L('Layout')></b><!field select page.template layouts></div>
<br style='clear:both;'><b><!=L('Meta data')></b><!field cmsmeta page.meta>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>
