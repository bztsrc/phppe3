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
.accept {
	cursor:pointer;
	position:fixed;
	bottom:5px;
	right:5px;
	z-index:1001;
}
</style>
<!if core.isError()><div style='padding:3px;'><!include errorbox></div><!/if><!form page>
<!field hidden height>
<!field hidden pe.try1>
<!=field>
<div style='padding:4px;'>
<!if editable>
<button class='accept btn btn-link'><img src='images/cms/accept.png' alt='OK' class='accept'></button>
<!else>
<span class='accept' style='color:red;cursor:not-allowed;'><!=L("Page is locked!")></span>
<!/if>
<div style='left:4px;bottom:4px;position:fixed;color:#d0d0d0;text-shadow:#000 2px 2px 3px;'><!=L(fieldTitle)></div></form>
</div>
