<style>
INPUT.input, SELECT.input, TEXTAREA.input {
	width:<!=intval(_REQUEST['w']-1)>px !important;
}
TEXTAREA.input {
	min-height:<!=_REQUEST['h']>px !important;
}
.maxheight {
	height:<!=_REQUEST['h']>px !important;
}
#param_edit .setsel_box {
	border:inset 1px #404040;background:rgba(64,64,64,0.8);
	height:<!=intval(_REQUEST['h']-32)>px !important;
}
</style>
<!form param>
<!field hidden w>
<!field hidden h>
<div id='param_edit'><!template><%field <!=type> app.value><!/template></div>
<!field hidden pe.try1>
<div style='padding:4px;color:#d0d0d0;text-shadow:#000 2px 2px 3px;'>
<!if page['ownerid']==user.id||page['ownerid']==0>
<img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["param"].submit();'>
<!else>
<span style='float:right;color:#F00000;'><!=L("Page is locked!")></span>
<!/if>
<div style='left:4px;bottom:6px;position:fixed;'><!=param['title']></div></form></div>