<style type='text/css' scoped>
BODY { background:transparent !important;}
INPUT.input, SELECT.input, TEXTAREA.input {
	width:<!=intval(_REQUEST['w'])>px !important;
	height:<!=_REQUEST['h']>px !important;
    border:0px;
    border-radius:0px;
    padding:0px;
    margin:0px;
}
#param_edit .setsel_box {
	border:inset 1px #404040;background:rgba(64,64,64,0.8) !important;
	height:<!=intval(_REQUEST['h']-32)>px !important;
}
</style>
<!form param>
<!field hidden w>
<!field hidden h>
<!field hidden pe.try1>
<!=field><div style='padding:4px;color:#d0d0d0;text-shadow:#000 2px 2px 3px;'>
<!if page['ownerid']==user.id||page['ownerid']==0>
<img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["param"].submit();'>
<!else>
<span class='accept' style='color:red;'><!=L("Page is locked!")></span>
<!/if>
<div style='left:4px;bottom:4px;position:fixed;'><!=L(fieldTitle)></div></form></div>
