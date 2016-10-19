<style scoped>
BODY { background:transparent !important;color:#B0B0B0;}
INPUT.input, TEXTAREA.input, SELECT.input { background: rgba(32,32,32,0.95); color:#fff !important; }
INPUT.reqinput, TEXTAREA.reqinput, SELECT.reqinput { background: rgba(48,32,32,0.95); color:#fff !important; }
</style>
<div class='infobox' style='padding:5px;overflow:auto;'>
<span style='width:360px;display:inline-block;'><b><!=L("Modified at")></b></span>
<span style='width:160px;display:inline-block;'><b><!=L("Modified by")></b></span>
<span style='width:160px;display:inline-block;'><b><!=L("Published by")></b></span>
<!foreach versions>
<div style='cursor:pointer;z-index:9;display:block;width:100%;' onmouseover='this.style.background="#808080";' onmouseout='this.style.background="";' onclick='top.document.location.href="<!if IDX!=1><!=url("cms","archive")+""+urlencode(id)>?created=<!=urlencode(created)>&diff<!else><!=url(id)><!/if>";'>
<span style='width:360px;display:inline-block;<!if publishid==0>color:rgb(240,173,78) !important;<!/if>'>
<!time created> (<!difftime strtotime(created)-strtotime(ct)>)</span>
<span style='width:160px;display:inline-block;<!if publishid==0>color:rgb(240,173,78) !important;<!/if>'>
<!if modifyid==-1>admin<!else><!if moduser><!=moduser><!else>?<!/if><!/if>
</span>
<span style='width:160px;display:inline-block;<!if publishid==0>color:rgb(240,173,78) !important;<!/if>'>
<!if publishid==-1>admin<!else><!if pubuser><!=pubuser><!else>&Oslash;<!/if><!/if>
</span>
</div>
<!/foreach>
</div>