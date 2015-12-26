<style>
B {
	display:block;
	margin-top:8px;
}
</style>
<div class='confpanel'>
<!form page>
<h2><!=L("Publish")></h2>
<b><!=L('Not Before')></b><!field time page.pubd><br/>
<b><!=L('Not After')></b><!field time page.expd><!if quickhelp><small><br/><!L help_publish></small><!/if><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


