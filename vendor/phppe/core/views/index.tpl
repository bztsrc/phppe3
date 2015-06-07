<!--
 @file phppe/views/index.tpl
 @author bzt@phppe.org
 @date 1 Jan 2015
 @brief Self test view for PHPPE Core
-->
<style type='text/css' scoped>
 BODY {height:100%;background:#ffffff;margin:0px;padding:0px;}
 BODY,TD,DIV,#content {font-family:sans-serif;font-size:14px;line-height:15px;}
 TD {padding:2px 2px 2px 2px;}
 IMG {border: 0px;}
 A {text-decoration:none;color:#000000;}
 A:hover {text-decoration:underline;}
 PRE {margin: 0px;}
 #content {margin: 10px;}
 .heading {font-weight:bold;background:#D0D0D0;padding:3px;}
 .comment {font-family:times;font-style:italic;color:#A0A0A0;}
 .bluebg {background:#A0A0F0;}
 .popup {box-shadow: 3px 3px 8px #000000;}
 .printonly {display:none;}
 .screenonly {display:block;}
@media print {
 BODY {page:portrait;}
 SELECT,INPUT,TEXTAREA {border-top:0px none;border-left:0px none;border-right:0px none;border-bottom:dotted 1px #000000;font-weight: bold;}
 .printonly {display:block;}
 .screenonly {display:none;}
}
</style>
<div class="toc"></div>
<!if core.isinst('highlight')><!widget highlight(0,200) txt php perl cpp><!/if>
<!if core.isinst('popup')><!widget popup><!/if>
<!if core.isinst('zoom')><!widget zoom(60,80)><!/if>
<!if core.isinst('dnd')><!widget dnd><!/if>
<h1>PHPPE3 Self Test and Cheat Sheet Page</h1>
<a href='http://validator.w3.org/check?uri=referer' target='_blank'>W3C HTML Validator</a> |
<a href='http://jigsaw.w3.org/css-validator/check/referer' target='_blank'>W3C CSS Validator</a><br><br>
<div class='heading'>Environment</div>
<table>
<tr><td>runlevel:</td><td>
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
<tr><td>Data domain:</td><td><!if core.lib('DS').primary><span style="color:green;"><!=core.lib('DS').primary></span><!else><span style="color:blue;">files</span><!/if>
</td><td class='comment'>
<!if core.lib('DS').primary>Primary datasource<!else>Local files only in <i>data</i> directory<!/if>
</td></tr>
<tr><td>Diagnostic mode:</td><td>
<!if core.diag><span style='color:red;'>ON</span><!else><span style='color:green;'>OFF</span><!/if>
</td><td class='comment'><!if core.diag>Create config.php to quit mode. The directory tree is checked for valid file permissions.<!else>You are okay. Only the document root's permissions and self consistency checked.<!/if></td></tr>
<tr><td>Edit mode:</td><td><!if _SESSION['pe_edit']><span style='color:red;'>ON</span><!else><span style='color:green;'>OFF</span><!/if></td><td class='comment'>Controls whether &lt;!VAR> hooks "show" or "edit" method</td></tr>
<tr><td>Conf mode:</td><td><!if _SESSION['pe_conf']><span style='color:red;'>ON</span><!else><span style='color:green;'>OFF</span><!/if></td><td class='comment'>Controls whether &lt;!WIDGET> hooks "show" (widget face) or "edit" (configure) method</td></tr>
<tr><td>Browser:</td><td><!=client.agent></td><td class='comment'>Your browser's type</td></tr>
<tr><td>Client:</td><td><!if client.user><!=client.user><!else>(no http auth)<!/if>, <!=client.lang>, <!=client.tz>, <!=implode('x',client.screen)></td><td class='comment'>Your authenticated user, your browser's language, timezone and screen size</td></tr>
<tr><td>Remote address:</td><td><!=client.ip></td><td class='comment'>Your real remote ip address (works if webserver is behind a http proxy or a load balancer)</td></tr>
<tr><td>User:</td><td><!if user.id><!=user.name> #<!=user.id><!else>none<!/if></td><td class='comment'>For databaseless config, there's still an 'admin' user</td></tr>
<tr><td colspan='2' class='comment'>&lt;!-- MONITORING: <!if count(core.error)==0><!if core.runlevel==0><span style='color:olive;'>OK</span><!else><span style='color:salmon;'>WARNING</span><!/if><!else><span style='color:brown;'>ERROR</span><!/if>, page <s>0.0100</s> sec, db <s>0.000</s> sec, server <s>0.000</s> sec, mem <s>0.000</s> mb --></td><td class='comment'>There's a comment similar to the left just before the &lt;/body> tag.<br>OK - page was generated successfully in production mode.<br>WARNING - page was generated successfully, but in developer or debug mode, producing tons of logs.<br>ERROR - something bad happened (for example a validator returned false).</td></tr>
</table>
<br>
<div class="heading">Flow test</div>
<table>
<tr><td>&lt;!foreach strings>&lt;!=KEY>:&lt;!=VALUE>&lt;br>&lt;!/foreach></td><td><!foreach strings><!=KEY>:<!=VALUE><br><!/foreach></td><td class='comment'>Example iteration on a plain array</td></tr>
<tr><td>&lt;!foreach core.addons><br>&nbsp;&nbsp;&lt;!=IDX>(&lt;!=ODD>)&lt;!=KEY>:&lt;!=name>&lt;br><br>&lt;!/foreach><br><br></td><td><!foreach core.addons><!=IDX>(<!=ODD>) <!=KEY>:<!=name><br><!/foreach></td><td class='comment'>Example iteration on an array of objects</td></tr>
<tr><td>&lt;!if core.needframe>normal mode&lt;!else>fullscreen mode&lt;!/if></td><td><!if core.needframe>normal mode<!else>fullscreen mode<!/if></td><td class='comment'>Expression dependent branches of output</td></tr>
<tr><td>&lt;!if core.needframe><br>&nbsp;&nbsp;&lt;!foreach core.addons><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;!if ODD><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!=KEY>, &lt;!foreach strings>&lt;!=KEY>&lt;!/foreach><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;!else><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!foreach core.js>&lt;!=KEY>,&nbsp;&lt;!=parent.KEY>&lt;!/foreach><br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if><br>&nbsp;&nbsp;&lt;!/foreach><br>&lt;!else>should not see this&lt;!/if></td><td>
<!if core.needframe>
  <!foreach core.addons>
    <!if ODD><!=KEY>, <!foreach strings><!=KEY><!/foreach><br><!else>
      <!foreach core.js><!=KEY>, <!=parent.KEY><br><!/foreach>
    <!/if>
  <!/foreach>
<!else>should not see this<!/if></td><td class='comment'>Nested flow control structures</td></tr>
<tr><td>&lt;!include 404></td><td style='padding-bottom:5px;'><div style='border:1px dotted #000000;'><!include 404></div></td><td class='comment'>Include other template</td></tr>
<tr><td>&lt;!template> &lt;%=(&lt;!=core.now>+1)> &lt;/template></td><td style='padding-bottom:5px;'><!template><%=(<!=core.now>+1)><!/template></td><td class='comment'>Generate template tags with templates, use &lt;% instead of &lt;! for second iteration</td></tr>
</table>
<br><br>
<div class="heading">Output test</div>
<table>
<tr><td>&lt;!=core.id></td><td><!=core.id></td><td class='comment'>Output the value of a field of an object</td></tr>
<tr><td>&lt;!=core.now></td><td><!=core.now></td><td class='comment'>Current UNIX timestamp (seconds since 01/01/1970 00:00:00 UTC)</td></tr>
<tr><td>&lt;!=(core.now/100+core.needframe)></td><td><!=(core.now/100+core.needframe)></td><td class='comment'>Output the result of an expression (uses eval)</tr>
<tr><td>&lt;!=sprintf('%012d',core.now/123)></td><td><!=sprintf('%012d',core.now/123)></td><td class='comment'>Output result with formatting applied</td></tr>
<tr><td>&lt;!date core.now></td><td><!date core.now></td><td class='comment'>Output timestamp in localized human readable format</tr>
<tr><td>&lt;!time core.now></td><td><!time core.now></td><td class='comment'>Your browser's timezone is: <!=_SESSION['pe_tz']></td></tr>
<tr><td>&lt;!L Cancel></td><td><!L Cancel></td><td class='comment'>Expected to be translated to your browser's language (<!=client.lang>)</tr>
<tr><td>&lt;!L click_me></td><td><!L click_me></td><td class='comment'>Should read "NA_click_me"</td></tr>
<tr><td>&lt;!=L("click_me")></td><td><!=L("click me")></td><td class='comment'>Should read "click me"</td></tr>
<tr><td>&lt;!if core.istry()>&lt;!dump obj 1>&lt;!/if></td><td><!if core.istry()><!dump obj 1><!/if></td><td class='comment'>Built-in object dumper for debugging. Press the OK button below the form to see it in action.</td></tr>
<tr><td>&lt;!nonexistent tag></td><td><!nonexistent tag></td><td class='comment'>Should display a warning</td></tr>
</table>
<br><br>
<div class='heading'>Input test</div>
<!form obj>
<table>
<tr><td>&lt;!var text obj.summary></td><td style='padding-bottom:5px;'><!var text obj.summary></td><td class='comment'>Acts as a field in edit mode, outputs the formatted value otherwise</td></tr>
<tr><td>&lt;!var time core.now></td><td style='padding-bottom:5px;'><!var time core.now></td><td class='comment'>Acts as a field in edit mode, outputs timestamp in a localized human readable format otherwise</td></tr>
<tr><td>&lt;!field *text obj.field0></td><td><!field *text obj.field0></td><td class='comment'>Simple string input, no restrictions, required</td></tr>
<tr><td>&lt;!field pass obj.field1></td><td><!field pass obj.field1></td><td class='comment'>Simple password</td></tr>
<tr><td>&lt;!field text(<span title='size'>8</span>,<span title='maxlength'>32</span>) obj.field2 - - - Search></td><td><!field text(8,32) obj.field2 - - - Search></td><td class='comment'>Reads more than meets the eye with &quot;fake&quot; value</td></tr>
<tr><td>&lt;!field text(<span title='size'>20</span>,<span title='maxlength'>80</span>,<span title='rows'>2</span>)  obj.field3></td><td><!field text(20,80,2) obj.field3></td><td class='comment'>You don't have to know about textarea, just add the number of rows you want</td></tr>
<tr><td>&lt;!field select obj.field4 <span title='list values'>core.addons</span>></td><td><!field select obj.field4 core.addons></td><td></td></tr>
<tr><td>&lt;!field select(<span title='size'>3</span>,<span title='is multiple'>1</span>) obj.field5 <span title='list values'>core.addons</span>></td><td><!field select(3,1) obj.field5 core.addons></td><td></td></tr>
<tr><td>&lt;!field date(<span title='years behind'>5</span>,<span title='years to come'>2</span>) obj.field6></td><td><!field date(5,2) obj.field6></td><td class='comment'>Hint: change date format in phppe/lang/<!=client.lang>.php, and see what happens ;-)</td></tr>
<tr><td>&lt;!field time(<span title='years behind'>3</span>,<span title='years to come'>3</span>,<span title='steps in minutes'>5</span>) obj.field7></td><td><!field time(3,3,5) obj.field7></td><td class='comment'>Date plus time</td></tr>
<tr><td>&lt;!field phone obj.field8></td><td><!field phone obj.field8></td><td class='comment'>Only valid characters accepted</td></tr>
<tr><td>&lt;!field email obj.field9></td><td><!field email obj.field9></td><td class='comment'>Field value checked on blur</td></tr>
<tr><td>&lt;!field check obj.field10 <span title='multilang label'>clickme</span>></td><td><!field check obj.field10 clickme></td><td></td></tr>
<tr><td>&lt;!field radio(<span title='numeric value'>1</span>) obj.field11 <span title='multilang label'>one</span>> &lt;!field radio(<span title='numeric value'>2</span>) obj.field11 <span title='multilang label'>two</span>></td><td><!field radio(1) obj.field11 one> <!field radio(2) obj.field11 two></td><td class='comment'>Note the automatic clickable labels</td></tr>
<tr><td>&lt;!field radio(<span title='string value'>'one'</span>) obj.field12 <span title='multilang label'>one</span>> &lt;!field radio(<span title='string value'>'two'</span>) obj.field12 <span title='multilang label'>two</span>></td><td><!field radio('one') obj.field12 one> <!field radio('two') obj.field12 two></td><td></td></tr>
<tr><td>&lt;!field num obj.field13></td><td><!field num obj.field13></td><td class='comment'>Value right aligned, and only numbers accepted</td></tr>
<tr><td>&lt;!field num(8,4) obj.field14></td><td><!field num(8,4) obj.field14></td><td class='comment'>Reads number up till 9999</td></tr>
<tr><td>&lt;!field num(8,4,100,9000) obj.field15></td><td><!field num(8,4,100,9000) obj.field15></td><td class='comment'>On the fly boundary check</td></tr>
<tr><td>&lt;!field file obj.field16></td><td><!field file obj.field16></td><td class='comment'>File upload</td></tr>
<!if core.isinst('checklist')>
<tr><td>&lt;!field checklist(1,2,3,4,5) obj.field17 <span title='list values'>core.addons</span>></td><td><!field checklist(1,2,3,4,5,6,7) obj.field17 core.addons></td><td class='comment'>A checklist implemented as field plugin</td></tr>
<!/if>
<!if core.isinst('rs')>
<tr><td>&lt;!field rs obj.field18></td><td><!field rs obj.field18></td><td class='comment'>Simple record set (check field_datagrid for more)</td></tr>
<!/if>
<!if core.isinst('combo')>
<tr><td>&lt;!field combo obj.field19 <span title='list values'>core.addons</span>></td><td><!field combo obj.field19 core.addons></td><td class='comment'>Combo Box. Select "Other" for text mode, press down arrow or backspace with empty value to return.</td></tr>
<!/if>
<tr><td>&lt;!field cancel> &lt;!field update> &lt;!field update Save></td><td><!field cancel> <!field update> <!field update Save></td><td class='comment'>Form post control</td></tr>
<tr><td>&lt;!field something obj.fieldX></td><td><!field something obj.fieldX></td><td class='comment'>What happens if field type not known</td></tr>
</table>
<br><br>
<div class='heading'>Library test</div>
<table><tr><td>PHPPE Pack<!if !file_exists('phppe/00_core.php')> not<!/if> installed.</td>
<td><!if !user.id><a href='login'>Login</a><!else><a href='logout'>Logout</a><!/if></td><td></td></tr>
<tr><td>&lt;!widget popup></td><!if !core.isinst('popup')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;">popup not installed.</span></td><!else><td><span style='border:1px solid black;padding:3px;' onmouseover='popup_open(this,"mymenu",10,10);'>over me</span>
<div id='mymenu' class='popup' style='position:absolute;background:#A0A0A0;padding:5px;z-index:10;display:none;'>one<br>two<br>three<br>four<br>...<br></div>
<br><br></td><!/if><td class='comment'>Popup title support</td></tr>
<tr><td>&lt;!widget zoom(60,80)><br>&lt;img id='thumb0'... &lt;div id='thumb1'...</td><!if !core.isinst('zoom')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;">zoom not installed.</span></td><!else><td><img id='thumb0' alt='' width='60' src='http://phppe.org/phppe3_extmgr.png' title='screenshot of PHPPE Extension Manager'>&nbsp;&nbsp;&nbsp;<div id='thumb1' title='div to zoom' onclick='zoom_open("1");' style='cursor:pointer;'>click me</div><br>
<div id='large1' class='bluebg' style='display:none;'>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br>sdf;laskdf asdf asdf sadf asdf<br>adasdsd asd asd asdasdas<br>adasdasdas asd asd<br><br><br><br><br><br></div>
</td><!/if><td class='comment'>Example field plugin that has only js part.</td></tr>
<tr><td>&lt;!widget dnd></td><!if !core.isinst('dnd')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;">dnd not installed.</span></td><!else><td><img id='dndtestimg' src='?cache=logo' alt='dragable image' onmousedown='return dnd_drag("tagName: img, id: "+this.id+",\nsrc: "+this.src,this,32);' style='cursor:move;padding-right:20px;' width='42'> <span style='border:1px solid black;padding:5px;cursor:copy;' onmouseup='return dnd_drop(event,alert);'>drag and drop picture here</span><br><br></td><!/if><td class='comment'>Yet another fancy js stuff</td></tr>
<tr><td>&lt;!widget highlight(0,200) txt php perl python cpp)><br>&lt;pre class='php'>...&lt;/pre></td>
<!if !core.isinst('highlight')>
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;">highlight not installed.</span></td>
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
<td><span style="background:#F00000;color:#FFA0A0;padding:3px;">tetris not installed.</span></td>
<!else><td><a id='thumb2' onclick='zoom_open("2",400,380);' style='cursor:pointer;'>&lt;iframe>&lt;!field tetris>&lt;/iframe></a><div id='large2' style='display:none;'><iframe width='400' height='388' style='border:solid 0px;overflow-x:hidden;overflow-y:hidden;' src='tetris'></iframe></div></td><!/if><td class='comment'>A little fun.<br>Note this is a non-PHPPE compliant third party code,<br>if you put it on a page, other js handlers will die.<br>You have to use an iframe inside the div to avoid js handler conflicts.</td></tr>
<tr><td>&lt;!widget rte(800,100) obj.rte></td><!if !core.isinst('rte')>
<td colspan='2'><span style="background:#F00000;color:#FFA0A0;padding:3px;">rte not installed.</span></td><!else><td colspan='2'><!field rte(800,100) obj.rte></td><!/if></tr>
</table>
</form>
<br><br>
<div class='heading'>Environment dump</div>
<div style='overflow:auto;height:300px;'><!dump core></div>
<div><hr><!dump client><hr><!dump user><hr><!dump _SERVER></div>
<br><br>
