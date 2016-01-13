<!--
 @file phppe/views/index.tpl
 @author bzt@phppe.org
 @date 1 Jan 2016
 @brief Self test view for PHPPE Core
-->
<style type='text/css' scoped>
 BODY {height:100%;background:#fff;margin:0px;padding:0px;}
 BODY,TD,DIV,#content {font-family:sans-serif;font-size:14px;line-height:15px;}
 H3 {margin:2px;}
 TD {padding:2px;}
 IMG {border:0px;}
 A {text-decoration:none;color:#000;}
 A:hover {text-decoration:underline;}
 PRE {margin: 0px;}
 #content {margin: 10px;}
 .heading {font-weight:bold;background:#D0D0D0;padding:3px;}
 .comment {font-family:times;font-style:italic;color:#A0A0A0;}
 .bluebg {background:#A0A0F0;}
 .popup {box-shadow: 3px 3px 8px #000;}
 .printonly {display:none;}
 .screenonly {display:block;}
 TEXTAREA.wysiwyg {width:100%;height:100px;}
 .resptable { width:100%; }
 .resptable TH,TD {min-width:20px !important;}
 .resptable_pager { background:rgba(255,255,255,0.8); }
 DIV.wysiwyg_edit{border:solid 1px #808080;}
 DIV.wysiwyg_edit TD {border:dotted 1px #000 !important;}
 DIV.wysiwyg_edit TH {border:dotted 1px #000 !important;}
 DIV.wysiwyg_edit TABLE {width:100%;}
 DIV.wysiwyg_edit A {color:#0000ff !important;}
@media print {
 BODY {page:portrait;}
 SELECT,INPUT,TEXTAREA {border-top:0px none;border-left:0px none;border-right:0px none;border-bottom:dotted 1px #000;font-weight: bold;}
 .printonly {display:block;}
 .screenonly {display:none;}
}
</style>
<div class="toc"></div>
<!if core.isinst('highlight')><!widget highlight(0,200) txt php perl cpp><!/if>
<!if core.isinst('popup')><!widget popup><!/if>
<!if core.isinst('zoom')><!widget zoom(60,80)><!/if>
<!if core.isinst('dnd')><!widget dnd><!/if>
<div class='mosaicbox'><h1><!=L("PHPPE3 Self Test and Cheat Sheet Page")></h1>
<a href='http://validator.w3.org/check?uri=referer' target='_blank'>W3C HTML <!=L("Validator")></a> |
<a href='http://jigsaw.w3.org/css-validator/check/referer' target='_blank'>W3C CSS <!=L("Validator")></a><br><br>
</div>
<!if core.isError()>
<div class='mosaicbox' style='border:red 1px solid;border-radius:5px;padding:5px;margin:10px;background:#FFC0C0;color:red;'>
	<b><!=L("Form validation error!")></b><br/>
	<!foreach core.error()>
		<!foreach VALUE>
			&nbsp;&nbsp;<!=VALUE><br/>
		<!/foreach>
	<!/foreach>
</div>
<!/if>
<div class='mosaicbox'>
<div class='heading'><h3><!=L("Environment")></h3></div>
<table>
<tr><td>runlevel:</td><td dir='ltr'>
<!if core.runlevel==0><span style='color:green;'><!/if>
<!if core.runlevel==1><span style='color:blue;'><!/if>
<!if core.runlevel==2><span style='color:red;'><!/if>
<!if core.runlevel==3><span style='color:gray;'><!/if><!=core.runlevel></span>
</td><td class='comment'>
<!if core.runlevel==0>Production mode. Normal<!/if>
<!if core.runlevel==1>Verbose mode. Testing verbosity, check phppe/log<!/if>
<!if core.runlevel==2>Developer mode. Extra verbose level, warnings on screen<!/if>
<!if core.runlevel==3>Debug mode. Insane verbose level, warnings on screen<!/if>
</td></tr>
<tr><td>Data domain:</td><td dir='ltr'><!if core.lib('DS').primary><span style="color:green;"><!=core.lib('DS').primary></span><!else><span style="color:blue;">files</span><!/if>
</td><td class='comment'>
<!if core.lib('DS').primary>Primary datasource<!else>Local files only in <i>data</i> directory<!/if>
</td></tr>
<tr><td>Diagnostic mode:</td><td dir='ltr'>
<!if core.diag><span style='color:red;'><!=L("Enabled")></span><!else><span style='color:green;'><!=L("Disabled")></span><!/if>
</td><td class='comment'><!if core.diag>Create config.php to quit mode. The directory tree is checked for valid file permissions.<!else>You are okay. Only the document root's permissions and self consistency checked.<!/if></td></tr>
<tr><td>Edit mode:</td><td dir='ltr'><!if _SESSION['pe_e']><span style='color:red;'><!=L("Enabled")></span><!else><span style='color:green;'><!=L("Disabled")></span><!/if></td><td class='comment'>Controls whether &lt;!VAR> hooks "show" or "edit" method</td></tr>
<tr><td>Conf mode:</td><td dir='ltr'><!if _SESSION['pe_c']><span style='color:red;'><!=L("Enabled")></span><!else><span style='color:green;'><!=L("Disabled")></span><!/if></td><td class='comment'>Controls whether &lt;!WIDGET> hooks "show" (widget face) or "edit" (configure) method</td></tr>
<tr><td>Browser:</td><td dir='ltr'><!=client.agent></td><td class='comment'>Your browser's type</td></tr>
<tr><td>Client:</td><td dir='ltr'><!if client.user><!=client.user><!else>(no http auth)<!/if>, <!=client.lang>, <!=client.tz>, <!=implode('x',client.screen)></td><td class='comment'>Your authenticated user, your browser's language, timezone and screen size</td></tr>
<tr><td>Remote address:</td><td dir='ltr'><!=client.ip></td><td class='comment'>Your real remote ip address (works if webserver is behind a http proxy or a load balancer)</td></tr>
<tr><td>User:</td><td dir='ltr'><!if user.id><!=user.name> #<!=user.id><!else>none<!/if></td><td class='comment'>For databaseless config, there's still an 'admin' user</td></tr>
<tr><td colspan='2' class='comment' dir='ltr'>&lt;!-- MONITORING: <!if count(core.error)==0><!if core.runlevel==0><span style='color:olive;'>OK</span><!else><span style='color:salmon;'>WARNING</span><!/if><!else><span style='color:brown;'>ERROR</span><!/if>, page <s>0.0100</s> sec, db <s>0.000</s> sec, server <s>0.000</s> sec, mem <s>0.000</s> mb --></td><td class='comment'>There's a comment similar to the left just before the &lt;/body> tag.<br>OK - page was generated successfully in production mode.<br>WARNING - page was generated successfully, but in developer or debug mode, producing tons of logs.<br>ERROR - something bad happened (for example a validator returned false).</td></tr>
</table>
</div><div class='mosaicbox'>
<div class="heading"><h3><!=L("Flow test")></h3></div>
<table>
<tr><td dir='ltr'>&lt;!foreach strings>&lt;!=KEY>:&lt;!=VALUE>&lt;br>&lt;!/foreach></td><td><!foreach strings><!=KEY>:<!=VALUE><br><!/foreach></td><td class='comment'>Example iteration on a plain array</td></tr>
<tr><td dir='ltr'>&lt;!foreach core.addons><br>&nbsp;&nbsp;&lt;!=IDX>(&lt;!=ODD>)&lt;!=KEY>:&lt;!=name>&lt;br><br>&lt;!/foreach><br><br></td><td><!foreach core.addons><!=IDX>(<!=ODD>) <!=KEY>:<!=name><br><!/foreach></td><td class='comment'>Example iteration on an array of objects</td></tr>
<tr><td dir='ltr'>&lt;!if core.noframe>fullscreen mode&lt;!else>normal mode&lt;!/if></td><td><!if core.noframe>fullscreen mode<!else>normal mode<!/if></td><td class='comment'>Expression dependent branches of output</td></tr>
<tr><td dir='ltr'>&lt;!if !core.noframe><br>&nbsp;&nbsp;&lt;!foreach core.addons><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;!if ODD><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!=KEY>, &lt;!foreach strings>&lt;!=KEY>&lt;!/foreach><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;!else><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!foreach core.js>&lt;!=KEY>,&nbsp;&lt;!=parent.KEY>&lt;!/foreach><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if><br>&nbsp;&nbsp;&lt;!/foreach><br>&lt;!else>should not see this&lt;!/if></td><td>
<!if !core.noframe>
  <!foreach core.addons>
    <!if ODD><!=KEY>, <!foreach strings><!=KEY><!/foreach><br><!else>
      <!foreach core.js><!=KEY>, <!=parent.KEY><br><!/foreach>
    <!/if>
  <!/foreach>
<!else>should not see this<!/if></td><td class='comment'>Nested flow control structures</td></tr>
<tr><td dir='ltr'>&lt;!include 404></td><td style='padding-bottom:5px;'><div style='border:1px dotted #000000;'><!include 404></div></td><td class='comment'>Include other template</td></tr>
<tr><td dir='ltr'>&lt;!template> &lt;%=(&lt;!=core.now>+1)> &lt;/template></td><td style='padding-bottom:5px;'><!template><%=(<!=core.now>+1)><!/template></td><td class='comment'>Generate template tags with templates, use &lt;% instead of &lt;! for second iteration</td></tr>
</table>
</div><div class='mosaicbox'>
<div class="heading"><h3><!=L("Output test")></h3></div>
<table>
<tr><td dir='ltr'>&lt;!=core.id></td><td><!=core.id></td><td class='comment'>Output the value of a field of an object</td></tr>
<tr><td dir='ltr'>&lt;!=core.now></td><td><!=core.now></td><td class='comment'>Current UNIX timestamp (seconds since 01/01/1970 00:00:00 UTC)</td></tr>
<tr><td dir='ltr'>&lt;!=(core.now/100+(1-core.noframe))></td><td><!=(core.now/100+(1-core.noframe))></td><td class='comment'>Output the result of an expression (uses eval)</tr>
<tr><td dir='ltr'>&lt;!=sprintf('%012d',core.now/123)></td><td><!=sprintf('%012d',core.now/123)></td><td class='comment'>Output result with formatting applied</td></tr>
<tr><td dir='ltr'>&lt;!date core.now></td><td><!date core.now></td><td class='comment'>Output timestamp in localized human readable format</tr>
<tr><td dir='ltr'>&lt;!time core.now></td><td><!time core.now></td><td class='comment'>Your browser's timezone is: <!=_SESSION['pe_tz']></td></tr>
<tr><td dir='ltr'>&lt;!L Cancel></td><td><!L Cancel></td><td class='comment'>Expected to be translated to your browser's language (<!=client.lang>)</tr>
<tr><td dir='ltr'>&lt;!L click_me></td><td><!L click_me></td><td class='comment'>Should read "NA_click_me"</td></tr>
<tr><td dir='ltr'>&lt;!=L("click_me")></td><td><!=L("click me")></td><td class='comment'>Should read "click me"</td></tr>
<tr><td dir='ltr'>&lt;!if core.istry()>&lt;!dump obj>&lt;!/if></td><td><!if core.istry()><!dump obj><!/if></td><td class='comment'>Built-in object dumper for debugging. Press the OK button below the form to see it in action.</td></tr>
<tr><td dir='ltr'>&lt;!nonexistent tag></td><td><!nonexistent tag></td><td class='comment'>Should display a warning</td></tr>
</table>
</div><div class='mosaicbox'>
<div class='heading'><h3><!=L("Input test")></h3></div>
<!form obj>
<table>
<tr><td dir='ltr'>&lt;!var text obj.summary></td><td style='padding-bottom:5px;'><!var text obj.summary></td><td class='comment'>Acts as a field in edit mode, outputs the formatted value otherwise</td></tr>
<tr><td dir='ltr'>&lt;!var time core.now></td><td style='padding-bottom:5px;'><!var time core.now></td><td class='comment'>Acts as a field in edit mode, outputs timestamp in a localized human readable format otherwise</td></tr>
<tr><td dir='ltr'>&lt;!field *text obj.field0></td><td><!field *text obj.field0></td><td class='comment'>Simple string input, no restrictions, required</td></tr>
<tr><td dir='ltr'>&lt;!field pass obj.field1></td><td><!field pass obj.field1></td><td class='comment'>Simple password</td></tr>
<tr><td dir='ltr'>&lt;!field text(<span title='size'>8</span>,<span title='maxlength'>32</span>) obj.field2 - - - search></td><td><!field text(8,32) obj.field2 - - - search></td><td class='comment'>Reads more than meets the eye with &quot;fake&quot; value</td></tr>
<tr><td dir='ltr'>&lt;!field text(<span title='size'>20</span>,<span title='maxlength'>80</span>,<span title='rows'>2</span>)  obj.field3></td><td><!field text(20,80,2) obj.field3></td><td class='comment'>You don't have to know about textarea, just add the number of rows you want</td></tr>
<tr><td dir='ltr'>&lt;!field select obj.field4 <span title='list values'>core.addon()</span>></td><td><!field select obj.field4 core.addon()></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field select(<span title='size'>3</span>,<span title='is multiple'>1</span>) obj.field5 <span title='list values'>core.addons</span>></td><td><!field select(3,1) obj.field5 core.addons></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field date(<span title='years behind'>5</span>,<span title='years to come'>2</span>) obj.field6></td><td><!field date(5,2) obj.field6></td><td class='comment'>Hint: change date format in phppe/lang/<!=client.lang>.php, and see what happens ;-)</td></tr>
<tr><td dir='ltr'>&lt;!field time(<span title='years behind'>3</span>,<span title='years to come'>3</span>,<span title='steps in minutes'>5</span>) obj.field7></td><td><!field time(3,3,5) obj.field7></td><td class='comment'>Date plus time</td></tr>
<tr><td dir='ltr'>&lt;!field phone obj.field8></td><td><!field phone obj.field8></td><td class='comment'>Only valid characters accepted</td></tr>
<tr><td dir='ltr'>&lt;!field email obj.field9></td><td><!field email obj.field9></td><td class='comment'>Field value checked on blur</td></tr>
<tr><td dir='ltr'>&lt;!field *check obj.field10 <span title='multilang label'>sure</span>></td><td><!field *check obj.field10 sure></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field radio(<span title='numeric value'>1</span>) obj.field11 <span title='multilang label'>one</span>> &lt;!field radio(<span title='numeric value'>2</span>) obj.field11 <span title='multilang label'>two</span>></td><td><!field radio(1) obj.field11 one> <!field radio(2) obj.field11 two></td><td class='comment'>Note the automatic clickable labels</td></tr>
<tr><td dir='ltr'>&lt;!field radio(<span title='string value'>'one'</span>) obj.field12 <span title='multilang label'>one</span>> &lt;!field radio(<span title='string value'>'two'</span>) obj.field12 <span title='multilang label'>two</span>></td><td><!field radio('one') obj.field12 one> <!field radio('two') obj.field12 two></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field num obj.field13></td><td><!field num obj.field13></td><td class='comment'>Value right aligned, and only numbers accepted</td></tr>
<tr><td dir='ltr'>&lt;!field num(8,4) obj.field14></td><td><!field num(8,4) obj.field14></td><td class='comment'>Reads number up till 9999</td></tr>
<tr><td dir='ltr'>&lt;!field num(8,4,100,9000) obj.field15></td><td><!field num(8,4,100,9000) obj.field15></td><td class='comment'>On the fly boundary check</td></tr>
<tr><td dir='ltr'>&lt;!field file obj.field16></td><td><!field file obj.field16></td><td class='comment'>File upload</td></tr>
<!if core.isinst('checklist')>
<tr><td dir='ltr'>&lt;!field checklist(1,2,3,4,5) obj.field17 <span title='list values'>core.addons</span>></td><td><!field checklist(1,2,3,4,5,6,7) obj.field17 core.addons></td><td class='comment'>A checklist implemented as field plugin</td></tr>
<!/if>
<!if core.isinst('rs')>
<tr><td dir='ltr'>&lt;!field rs obj.field18></td><td><!field rs obj.field18></td><td class='comment'>Simple record set (check field_datagrid for more)</td></tr>
<!/if>
<!if core.isinst('combo')>
<tr><td dir='ltr'>&lt;!field combo obj.field19 <span title='list values'>core.addons</span>></td><td><!field combo obj.field19 core.addons></td><td class='comment'>Combo Box. Select "Other" for text mode, press down arrow or backspace with empty value to return.</td></tr>
<!/if>
<tr><td dir='ltr'>&lt;!field cancel> &lt;!field update> &lt;!field update Save></td><td><!field cancel> <!field update> <!field update Save></td><td class='comment'>Form post control</td></tr>
<tr><td dir='ltr'>&lt;!field button></td><td><!field button></td><td class='comment'>HTML5 button with javascript handler</td></tr>
<tr><td dir='ltr'>&lt;!field something obj.fieldX></td><td><!field something obj.fieldX></td><td class='comment'>What happens if field type not known</td></tr>
</table>
</div><div class='mosaicbox'>
<div class='heading'><h3><!=L("Library test")></h3></div>
<table width='100%'>
<tr><td dir='ltr'>PHPPE Pack<!if !file_exists('phppe/00_core.php')> not<!/if> installed.</td>
<td style='min-width:320px !important;'><!if !user.id><a href='login'><!=L("Login")></a><!else><a href='logout'><!=L("Logout")></a><!/if></td><td width='100%'></td></tr>
<tr><td dir='ltr'>&lt;!field boolean obj.field20></td><!if !core.isinst('boolean')><td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> boolean</span></td><!else><td><!field boolean obj.field20><!/if></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field boolean(true,'Enabled','Disabled') obj.field21></td><!if !core.isinst('boolean')><td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> boolean</span></td><!else><td><!field boolean(true,'Enabled','Disabled') obj.field21><!/if></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field notboolean obj.field22></td><!if !core.isinst('notboolean')><td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> notboolean</span></td><!else><td><!field notboolean obj.field22><!/if></td><td></td></tr>
<tr><td dir='ltr'>&lt;!field setsel obj.field23 core.addons></td><!if !core.isinst('setsel')><td colspan='2'><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> setsel</span></td><!else><td colspan='2'><!field setsel obj.field23 core.addons><!/if></td></tr>
<tr><td dir='ltr'>&lt;!widget popup></td><!if !core.isinst('popup')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> popup</span></td><!else><td><span style='border:1px solid black;padding:3px;' onmouseover='popup_open(this,"mymenu",10,10);'><!=L("over me")></span>
<div id='mymenu' class='popup' style='position:absolute;background:#A0A0A0;padding:5px;z-index:10;display:none;'><!L one><br><!L two><br><!L three><br><!L four><br>...<br></div>
<br><br></td><!/if><td class='comment'>Popup title support</td></tr>
<tr><td dir='ltr'>&lt;!widget zoom><br>&lt;img id='thumb0'... &lt;div id='thumb1'...</td><!if !core.isinst('zoom')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> zoom</span></td><!else><td><img id='thumb0' alt='' width='60' src='http://phppe.org/extmgr.png' rel='galery1' data-zoom='http://phppe.org/extmgr.png' data-zoom-max=80 data-zoom-min=60 title='<!=L("Screenshot")>'>&nbsp;&nbsp;&nbsp;<div id='thumb1' rel='galery1' title='<!=L("div to zoom")>' data-zoom='large1' style='cursor:pointer;' onselectstart='return dnd_drag(event,this.id,this);' onmousedown='return dnd_drag(event,this.id,this);'>click me</div><br>
<div id='large1' class='bluebg' style='display:none;'>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br></div>
</td><!/if><td class='comment'>Example field plugin that has only js part. It can stress thumbnails on hover. It also zooms large images or div elements.</td></tr>
<tr><td dir='ltr'>&lt;!widget dnd></td><!if !core.isinst('dnd')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> dnd</span></td><!else><td><img id='dndtestimg' src='?cache=logo' alt='dragable image' onmousedown='return dnd_drag(event,"tagName: img, id: "+this.id+",\nsrc: "+this.src,this);' style='cursor:move;padding-right:20px;' width='42'> <span style='border:1px solid black;padding:5px;cursor:copy;' onmouseup='return dnd_drop(event,alert);'><nobr><!=L("drag and drop picture here")></nobr></span><br><br></td><!/if><td class='comment'>Yet another fancy js stuff</td></tr>
<tr><td dir='ltr'>&lt;!widget highlight(0,200) txt php perl python cpp)><br>&lt;pre class='php'>...&lt;/pre></td>
<!if !core.isinst('highlight')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> highlight</span></td>
<!else><td><pre class='php'>var was=0,$groups=new Array("keyword","type","control");
$a = ($b==1?0:1); $s = "something";
/* multi
   line
   comment */
stdClass Object
[0] => stdClass Object	//one line comment
[Price] => 123
</pre></td><!/if><td class='comment'>Code block highlighter</td></tr>
<tr><td>&lt;iframe>&lt;!field tetris>&lt;/iframe></td><!if !core.isinst('tetris')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> tetris</span></td>
<!else><td><a id='thumb2' onclick='zoom_open("2",400,380);' style='cursor:pointer;'>&lt;iframe>&lt;!field tetris>&lt;/iframe></a><div id='large2' style='display:none;'><iframe width='400' height='388' style='border:solid 0px;overflow-x:hidden;overflow-y:hidden;' src='tetris'></iframe></div></td><!/if><td class='comment'>A little fun.<br>Note this is a non-PHPPE compliant third party code,<br>if you put it on a page, other js handlers will die.<br>You have to use an iframe inside the div to avoid js handler conflicts.</td></tr>
<tr><td dir='ltr'>&lt;!field wysiwyg obj.wsyiwyg></td><!if !core.isinst('wysiwyg')>
<td colspan='2'><span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> wysiwyg</span></td><!else><td colspan='2'><!field wysiwyg('100%') obj.wysiwyg></td><!/if></tr>
<tr><td dir='ltr'>&lt;!widget resptable></td><td colspan='2'>
<!if !core.isinst('resptable')>
<span style="background:#F00000;color:#FFA0A0;padding:3px;"><!=L("Not found")> resptable</span><!else><!widget resptable>
 <table class="resptable">
 <tr>
  <th data-fixed="1" style='cursor:pointer;'>#</th>
  <th><!=L("Name")></th>
  <th><!=L("Phone")></th>
  <th><!=L("Email")></th>
  <th><!=L("Address")></th>
  <th><!=L("Email")></th>
  <th><!=L("Email")></th>
 </tr>
 <tr>
  <td colspan='6'><b><!=L("Group")> 1</b></td>
 </tr>
 <tr>
  <td>1</td>
  <td>SomeBody1</td>
  <td>123456</td>
  <td>somebody1@nospam.com</td>
  <td>1111 x y 1</td>
  <td>somebody1@nospam.com</td>
  <td>somebody1@nospam.com</td>
 </tr>
 <tr>
  <td>2</td>
  <td>SomeBody2</td>
  <td>123456</td>
  <td>somebody2@nospam.com</td>
  <td>2222 x y 1</td>
  <td>somebody2@nospam.com</td>
  <td>somebody2@nospam.com</td>
 </tr>
 <tr>
  <td>3</td>
  <td>SomeBody3</td>
  <td>123456</td>
  <td>somebody3@nospam.com</td>
  <td>3333 x y 1</td>
  <td>somebody3@nospam.com</td>
  <td>somebody3@nospam.com</td>
 </tr>
 <tr>
  <td>4</td>
  <td>SomeBody4</td>
  <td>123456</td>
  <td>somebody4@nospam.com</td>
  <td>4444 x y 1</td>
  <td>somebody4@nospam.com</td>
  <td>somebody4@nospam.com</td>
 </tr>
 <tr>
  <td colspan='6'><b><!=L("Group")> 2</b></td>
 </tr>
 <tr>
  <td>5</td>
  <td>SomeBody5</td>
  <td>123456</td>
  <td>somebody5@nospam.com</td>
  <td>5555 x y 1</td>
  <td>somebody5@nospam.com</td>
  <td>somebody5@nospam.com</td>
 </tr>
 <tr>
  <td>6</td>
  <td>SomeBody6</td>
  <td>123456</td>
  <td>somebody6@nospam.com</td>
  <td>6666 x y 1</td>
  <td>somebody6@nospam.com</td>
  <td>somebody6@nospam.com</td>
 </tr>
 <tr>
  <td>7</td>
  <td>SomeBody7</td>
  <td>123456</td>
  <td>somebody7@nospam.com</td>
  <td>7777 x y 1</td>
  <td>somebody7@nospam.com</td>
  <td>somebody7@nospam.com</td>
 </tr>
 </table>
<!/if>
<span class='comment'>Mobile friendly responsive tables. Click the '#' table header for quick menu.</span>
<td>
</table>
</form>
</div><div><div class='mosaicbox' style='max-width:1024px;'>
<div class='heading'><h3><!=L("Environment dump")></h3></div>
<div style='overflow:auto;max-height:300px;'><!dump core></div>
</div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump client></div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump user></div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump _SERVER></div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump core.req2arr()></div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump array_reverse(get_declared_classes())></div>
<div class='mosaicbox' style='overflow:auto;max-height:300px;max-width:1024px;'><hr><!dump _SESSION></div>
</div>
<br><br>
