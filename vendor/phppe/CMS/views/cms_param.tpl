<style type='text/css' scoped>
BODY {
    background:transparent !important;
    padding:0px !important;
    margin:0px !important;
    width:100%;
    height:100%;
}
#page_value {
    width:100% !important;
    height:<!=app.height>px !important;
    border:0px !important;
    border-radius:0px !important;
    padding:0px !important;
    margin:0px !important;
}

<!if heightClass>
.<!=heightClass> {
    height:<!=(app.boxHeight?app.boxHeight:app.height)>px !important;
}
<!/if>
.infobox { height:<!=(app.boxHeight?app.boxHeight:app.height)>px !important; overflow:auto; padding:4px;}
.infobox DIV { display:inline-block; float:left; width:50%; padding:2px; }
.infobox SPAN { color:#d0d0d0 !important; }
.infobox TD, .infobox B { color:#d0d0d0 !important; text-shadow: #000 2px 2px 3px; }
.infobox INPUT.input, .infobox TEXTAREA.input, .infobox SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
.infobox INPUT.reqinput, .infobox TEXTAREA.reqinput, .infobox SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
.accept {
    cursor:pointer;
    position:fixed;
    bottom:5px;
    right:5px;
}
.setsel_filters { text-align:right; }
.setsel_box { border:inset 1px; width:50%; float:left; }
</style>
<!form page>
<!field hidden height>
<!field hidden pe.try1>
<!if core.isError()><div style='padding:3px;'><!include errorbox></div><!/if><!=field>
<div style='padding:4px;'>
<!if editable>
<img src='images/cms/accept.png' alt='OK' class='accept' onclick='return document.forms["page"].submit();'>
<!else>
<span class='accept' style='color:red;cursor:not-allowed;'><!=L("Page is locked!")></span>
<!/if>
<div style='left:4px;bottom:4px;position:fixed;color:#d0d0d0;text-shadow:#000 2px 2px 3px;'><!=L(fieldTitle)></div></form>
</div>
