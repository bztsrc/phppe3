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
<h2><!=L("Page Filters")></h2>
<b><!=L('Filters')></b><!field text page.filters><!if quickhelp><small><br/>Comma separated filters or ACEs, like: @loggedin,loggedin</small><!/if><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


