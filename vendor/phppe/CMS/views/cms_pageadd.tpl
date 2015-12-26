<style>
B {
	display:block;
	margin-top:8px;
}
</style>
<div class='confpanel'>
<!form page>
<h2><!=L(page.id?"Clone page":"Add new page")></h2>
<b><!=L('URL')></b><nobr><!if quickhelp><i><!=url("/")></i><!field *text page.id - confurl></nobr><!else><!field *text page.id><!/if><br/>
<b><!=L("Name")><!if quickhelp><small><i style='font-weight:normal;'> (<!=L("Browser title")>)</i></small><!/if></b><!field *text page.name><br/>
<b><!=L("Language")></b><!field select page.lang app.langs><!if quickhelp><small><i>  (<!=L("You have a languageless option too")>)</i></small><!/if><br/>
<b><!=L("Layout")></b><!field *select page.template app.layouts><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


