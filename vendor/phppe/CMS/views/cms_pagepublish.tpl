<style>
B {
	display:block;
	margin-top:8px;
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
<h2><!=L("Publish")></h2>
<div><b><!=L('Not Before')></b><!field time page.pubd></div>
<div><b><!=L('Not After')></b><!field time page.expd></div>
<!if quickhelp><small><br style='clear:both;'/><!L help_publish></small><!/if>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


