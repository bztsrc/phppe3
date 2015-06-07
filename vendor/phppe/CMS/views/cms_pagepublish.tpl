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
<h2><!=L("Page Publication")></h2>
<b><!=L('Not Before')></b><!field time page.pubd><br/>
<b><!=L('Not After')></b><!field time page.expd><!if quickhelp><small><br/>If Before date bigger or equals to After date, the page will be published immediately</small><!/if><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


