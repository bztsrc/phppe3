<style>
B {
	display:block;
}
.cal_div {
	background:rgba(32,32,32,0.9);
	color:#D0D0D0;
}
.cal_footer TD {
	background:rgba(32,32,32,0.9);
	color:#D0D0D0;
}
.cal_div SELECT {
	border:inset 1px #404040;
	background:rgba(64,64,64,0.9);
	color:#fff;
}
.cal_header {
	background:rgba(128,128,128,0.9);
}
.cal_selected {
	background:rgba(56,66,241,0.9);
	text-shadow:2px 2px 3px #000;
}
.cal_empty {
	background:rgba(16,16,16,0.9);
}
.cal_weekend {
	background:rgba(64,16,16,0.9);
}
.cal_workday {
	background:rgba(64,64,64,0.9);
}
</style>
<div class='confpanel blockform'>
<!form page>
<h2><!=L("Meta")></h2>
<b><!=L('URL')></b><nobr><span dir='ltr'><!if quickhelp><i><!=url("/")></i>&nbsp;<!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if></span><br/>
<div><b><!=L('Name')></b><!if quickhelp><small><!L help_pagename></small><!/if><!field *text page.name></div>
<div><b><!=L('Filters')></b><!if quickhelp><small><!L help_filters></small><!/if><!field text page.filter></div>
<br style='clear:both;'/>
<div><b><!=L('Language')></b><!field select page.lang langs></div>
<div><b><!=L('Layout')></b><!field select page.template layouts></div>
<div><b><!=L('Not Before')></b><!field time(1) page.pubd></div>
<div style='margin:5px 20px 5px 20px;'><b><!=L('Not After')></b><!field time(1) page.expd></div>
<!if quickhelp><small><br style='clear:both;'/><!L help_publish></small><!/if>
<br style='clear:both;'>
<b><!=L('Meta data')></b><!field cmsmeta page.meta>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


