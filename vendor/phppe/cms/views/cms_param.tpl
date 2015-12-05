<style>
.input {
	width:<!=intval(_REQUEST['w']-12)>px !important;
}
TEXTAREA.input {
	width:<!=intval(_REQUEST['w']-12)>px !important;
	min-height:<!=_REQUEST['h']>px !important;
}
</style>
<!form param>
<!field hidden w>
<!field hidden h>
<div id='param_edit' style='min-height:64px;'><!template><%field <!=type> app.value><!/template></div>
<!field hidden pe.try1>
<div style='padding:4px;color:#a0a0a0;text-shadow:#000 2px 2px 3px;'>
<!if page['ownerid']==user.id||page['ownerid']==0>
<img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["param"].submit();'>
<!else>
<span style='float:right;color:#F00000;'><!=L("Page is locked!")></span>
<!/if>
<!=param['title']></form></div>