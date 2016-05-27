<style type='text/css' scoped>
BODY {
    background:transparent !important;
    padding:0px !important;
    margin:0px !important;
    width:100%;
    height:100%;
}
#param_value {
    width:<!=app.sw> !important;
    height:<!=app.h>px !important;
    border:0px !important;
    border-radius:0px !important;
    padding:0px !important;
    margin:0px !important;
}
.accept {
    cursor:pointer;
    position:fixed;
    bottom:5px;
    right:5px;
}
</style>
<!form param>
<!field hidden w>
<!field hidden h>
<!field hidden pe.try1>
<!=field><div style='padding:4px;color:#d0d0d0;text-shadow:#000 2px 2px 3px;'>
<!if editable>
<img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["param"].submit();'>
<!else>
<span class='accept' style='color:red;cursor:not-allowed;'><!=L("Page is locked!")></span>
<!/if>
<div style='left:4px;bottom:4px;position:fixed;'><!=L(fieldTitle)></div></form></div>
