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
<h2><!=L("Page Dynamic Data Sets")></h2>
<b><!=L('Global')></b><br/>
<!field cmsdds frame.dds><br/>
<b><!=L('Local')></b><br/>
<!field cmsdds page.dds><br/>
<!field hidden pe.try1><img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
</form>
</div>


