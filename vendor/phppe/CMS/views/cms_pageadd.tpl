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
<h2><!=L(page.id?"Clone page":"Add new page")></h2>
<b><!=L('URL')></b><nobr><!if quickhelp><i><!=url("/")></i><!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if><br/>
<b><!=L("Name")></b><!field *text page.name><br/>
<b><!=L("Language")></b><!field *select page.lang app.langs><br/>
<b><!=L("Layout")></b><!field *select page.template app.layouts><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


