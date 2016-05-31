<!--
 @file vendor/phppe/Core/views/index.tpl
 @author bzt
 @date 1 Jan 2016
 @brief Self test view for PHPPE Core
-->
<style type='text/css' scoped>
<!if !core.isInst("bootstrap")>
@import url('http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
<!/if>
/* striped rows */
DIV.panel DIV.row { padding:2px; }
DIV.panel DIV.row:nth-of-type(even) { background:#f8f8f8; }
/* scrollable dumps */
DIV.dump PRE { overflow:auto;max-height:300px; }
/* defaults for set selection boxes */
.setsel_filters { text-align:right; }
.setsel_box { border: inset 1px; height:300px; width:50%; float:left; box-sizing:border-box !important; clear:none;}
.setsel_item { cursor:move; }
.setsel_img { display: inline; float:left; }
/* input with validation errors */
.errinput { background:#fedede; }
</style>
<div class="toc"></div>
<div class="container-fluid">
<h1><!=L("PHPPE3 Self Test and Cheat Sheet Page")></h1>
<p>
<a href='http://validator.w3.org/check?uri=referer' target='_blank'>W3C HTML <!=L("Validator")></a> |
<a href='http://jigsaw.w3.org/css-validator/check/referer' target='_blank'>W3C CSS <!=L("Validator")></a>
</p>
<!if core.isError()>
<div class='alert alert-danger'>
	<b><!=L("Form validation error!")></b><br/>
	<!foreach core.error()>
		<!foreach VALUE>
			&nbsp;&nbsp;<!=VALUE><br/>
		<!/foreach>
	<!/foreach>
</div>
<!/if>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Environment")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-2">Runlevel:</div>
		<div class="col-sm-4" dir="ltr">
<!if core.runlevel==0><span class="text-success"><!/if>
<!if core.runlevel==1><span class="text-info"><!/if>
<!if core.runlevel==2><span class="text-warning"><!/if>
<!if core.runlevel==3><span class="text-danger"><!/if><!=core.runlevel></span>
		</div>
		<div class="col-sm-6 text-muted small">
<!if core.runlevel==0>Production mode. Normal<!/if>
<!if core.runlevel==1>Verbose mode. Testing verbosity, check phppe/log<!/if>
<!if core.runlevel==2>Developer mode. Extra verbose level, warnings on screen<!/if>
<!if core.runlevel==3>Debug mode. Insane verbose level, warnings on screen<!/if>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Data domain:</div>
		<div class="col-sm-4" dir="ltr">
<!if core.lib('DS').name><span class="text-success"><!=core.lib('DS').name></span>
<!else><span class="text-info">files</span><!/if>
		</div>
		<div class="col-sm-6 text-muted small">
<!if core.lib('DS').name>Primary datasource<!else>Local files only in <i>data</i> directory<!/if>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Edit mode:</div>
		<div class="col-sm-4" dir="ltr">
<!if _SESSION['pe_e']><span class="text-danger"><!=L("Enabled")></span>
<!else><span class="text-info"><!=L("Disabled")></span><!/if>
		</div>
		<div class="col-sm-6 text-muted small">
Controls whether &lt;!VAR> tags hook "show" or "edit" method
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Configuration mode:</div>
		<div class="col-sm-4" dir="ltr">
<!if _SESSION['pe_c']><span class="text-danger"><!=L("Enabled")></span>
<!else><span class="text-info"><!=L("Disabled")></span><!/if>
		</div>
		<div class="col-sm-6 text-muted small">
Controls whether &lt;!WIDGET> hooks "show" (widget face) or "edit" (configure) method
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Browser:</div>
		<div class="col-sm-4 text-info" dir="ltr">
<!=client.agent>
		</div>
		<div class="col-sm-6 text-muted small">
			Your browser's type
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Client info:</div>
		<div class="col-sm-4 text-info" dir="ltr">
<!if client.user><!=client.user><!else>(no http auth)<!/if>, <!=client.lang>, <!=client.tz>, <!=implode('x',client.screen)>
		</div>
		<div class="col-sm-6 text-muted small">
			Your authenticated user, browser's language, timezone and screen size
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Remote address:</div>
		<div class="col-sm-4 text-info" dir="ltr">
<!=client.ip>
		</div>
		<div class="col-sm-6 text-muted small">
Your real remote ip address (works if webserver is behind a http proxy or a load balancer)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">Session user:</div>
		<div class="col-sm-4 text-info" dir="ltr">
<!if user.id><!=user.name> #<!=user.id><!else>none<!/if>
		</div>
		<div class="col-sm-6 text-muted small">
For databaseless config, there's still an 'admin' user
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 text-muted small">
&lt;!-- MONITORING: <!if count(core.error())==0><!if core.runlevel==0><span class="text-success">OK</span><!else><span class="text-warning">WARNING</span><!/if><!else><span class="text-danger">ERROR</span><!/if>, page <s>0.0100</s> sec, db <s>0.000</s> sec, server <s>0.000</s> sec, mem <s>0.000</s> mb -->
		</div>
		<div class="col-sm-6 text-muted small">
There's a comment similar to the left just before the &lt;/body> tag.<br>OK - page was generated successfully in production mode.<br>WARNING - page was generated successfully, but in developer or debug mode, producing tons of logs.<br>ERROR - something bad happened (for example a validator returned false).
		</div>
	</div>
  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Flow test")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-2">
&lt;!foreach _SESSION>&lt;!=KEY>:&lt;!=VALUE>&lt;br>&lt;!/foreach>
		</div>
		<div class="col-sm-4" dir="ltr">
<!foreach _SESSION><!=KEY>:<!=VALUE><br><!/foreach>
		</div>
		<div class="col-sm-6 text-muted small">
Example iteration on an array
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!foreach core.lib()><br>&nbsp;&nbsp;&lt;!=IDX>(&lt;!=ODD>)&lt;!=KEY>:&lt;!=name>&lt;br><br>&lt;!/foreach><br><br>
		</div>
		<div class="col-sm-4" dir="ltr">
<!foreach core.lib()><!=IDX>(<!=ODD>) <!=KEY>:<!=name><br><!/foreach>
		</div>
		<div class="col-sm-6 text-muted small">
Example iteration on an array of objects
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!if core.noframe> fullscreen mode &lt;!else> normal mode &lt;!/if>
		</div>
		<div class="col-sm-4 text-info" dir="ltr">
<!if core.noframe>fullscreen mode<!else>normal mode<!/if>
		</div>
		<div class="col-sm-6 text-muted small">
Expression dependent branches of output
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!include 404>
		</div>
		<div class="col-sm-4" dir="ltr">
<div style='border:1px dotted #000000;'><!include 404></div>
		</div>
		<div class="col-sm-6 text-muted small">
Include another template
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!template><br>&lt;%=(&lt;!=core.now>+1)> &lt;/template>
		</div>
		<div class="col-sm-4" dir="ltr">
<!template><%=(<!=core.now>+1)><!/template>
		</div>
		<div class="col-sm-6 text-muted small">
Generate template tags with templates, use &lt;% instead of &lt;! for second iteration
		</div>
	</div>

  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Output test")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-2">
&lt;!=core.now>
		</div>
		<div class="col-sm-4" dir="ltr">
<!=core.now>
		</div>
		<div class="col-sm-6 text-muted small">
Output the value of a property of an object. Should see current UNIX timestamp (seconds since 01/01/1970 00:00:00 UTC)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!=(core.now/100+ (1-core.noframe))>
		</div>
		<div class="col-sm-4" dir="ltr">
<!=(core.now/100+(1-core.noframe))>
		</div>
		<div class="col-sm-6 text-muted small">
Output the result of an expression (uses eval)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!=sprintf('%012d', core.now/123)>
		</div>
		<div class="col-sm-4" dir="ltr">
<!=sprintf('%012d',core.now/123)>
		</div>
		<div class="col-sm-6 text-muted small">
Output result with formatting using a function
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!date core.now>
		</div>
		<div class="col-sm-4" dir="ltr">
<!date core.now>
		</div>
		<div class="col-sm-6 text-muted small">
Output timestamp in localized human readable format
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!time core.now>
		</div>
		<div class="col-sm-4" dir="ltr">
<!time core.now>
		</div>
		<div class="col-sm-6 text-muted small">
Date and time. Your browser's timezone is: <!=_SESSION['pe_tz']>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!L Cancel>
		</div>
		<div class="col-sm-4" dir="ltr">
<!L Cancel>
		</div>
		<div class="col-sm-6 text-muted small">
Expected to be translated to your browser's language (<!=client.lang>)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!L click_me>
		</div>
		<div class="col-sm-4" dir="ltr">
<!L click_me>
		</div>
		<div class="col-sm-6 text-muted small">
Translate label. Should read "click me" (no translation specified)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!=L("click me")>
		</div>
		<div class="col-sm-4" dir="ltr">
<!=L("click me")>
		</div>
		<div class="col-sm-6 text-muted small">
Translate expression. Should read "click me" (no translation specified)
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!dump core.req2arr('obj')>
		</div>
		<div class="col-sm-4" dir="ltr">
<!dump core.req2arr('obj')>
<!if !core.isTry()><span class="text-warning">Press the "Save" button below the form to see it in action.</span><!/if>
		</div>
		<div class="col-sm-6 text-muted small">
Built-in object dumper for debugging.
		</div>
	</div>
  </div>
</div>

<!form obj>
<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Input test")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-2">
&lt;!var text obj.var>
		</div>
		<div class="col-sm-4" dir="ltr">
<!var text obj.var>
		</div>
		<div class="col-sm-6 text-muted small">
Acts as a field in edit mode, outputs the formatted value otherwise
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!widget text obj.widget>
		</div>
		<div class="col-sm-4" dir="ltr">
<!widget text obj.widget>
		</div>
		<div class="col-sm-6 text-muted small">
Shows widget configuration (edit method) in conf mode, outputs widget face (show method) otherwise.
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!cms *text cms0>
		</div>
		<div class="col-sm-4" dir="ltr">
<!cms *text cms0>
		</div>
		<div class="col-sm-6 text-muted small">
Shows a CMS edit icon when user is logged in has site administrator or web administrator access.
Otherwise just displays the formatted value of the given property. Icon onclick will raise a modal.
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!cms(400,100) text cms1>
		</div>
		<div class="col-sm-4" dir="ltr">
<!cms(400,100) text(200,10) cms1>
		</div>
		<div class="col-sm-6 text-muted small">
Specify dimensions of the modal, in case autodetection is not working for some reason. Without asterisk, the value is not shown.
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!cms(0,0,60) *text(200,2) cms2>
		</div>
		<div class="col-sm-4" dir="ltr">
<!cms(0,0,60) *text(200,2) cms2>
		</div>
		<div class="col-sm-6 text-muted small">
Occupy percentage of screen.
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!cms *wyswyg cms3>
		</div>
		<div class="col-sm-4" dir="ltr" style='background:#F0F0F0;'>
<!cms *wyswyg cms3>
		</div>
		<div class="col-sm-6 text-muted small">
AddOn to edit page parameter
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field text obj.field>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field text obj.field>
		</div>
		<div class="col-sm-6 text-muted small">
Shows edit hook.
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">
&lt;!field label obj.text0 Some_string>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field label obj.text0 Some_string>
		</div>
		<div class="col-sm-6 text-muted small">
Shows a translated label for a field.
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field *text obj.text0>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field *text obj.text0>
		</div>
		<div class="col-sm-6 text-muted small">
A manadatory field.
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field text(<span title='maxlength'>32</span>) obj.text1 - - - search>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field text(32) obj.text1 - - - search>
		</div>
		<div class="col-sm-6 text-muted small">
Text field with placeholder that accepts input up to 32 characters.
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field text(<span title='maxlength'>80</span>,<span title='rows'>2</span>) obj.text2>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field text(80,2) obj.text2>
		</div>
		<div class="col-sm-6 text-muted small">
You don&acute;t have to know about textarea, just add the number of rows you want
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field pass obj.pass - Enter_password>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field pass obj.pass - Enter_password>
		</div>
		<div class="col-sm-6 text-muted small">
Password input field
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field select obj.select0 <span title='list values'>core.addon()</span>>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field select obj.select0 core.addon()>
		</div>
		<div class="col-sm-6 text-muted small">
An option list feeded by a function
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field select(<span title='size'>3</span>,<span title='is multiple'>true</span>) obj.select1 <span title='list values'>_SERVER</span>>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field select(3,true) obj.select1 _SERVER>
		</div>
		<div class="col-sm-6 text-muted small">
Option list with more rows and multiple select options returning an array
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field phone obj.phone>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field phone obj.phone>
		</div>
		<div class="col-sm-6 text-muted small">
Phone number with input validation
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field email obj.email>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field email obj.email>
		</div>
		<div class="col-sm-6 text-muted small">
Email address with on blur validation
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;div class="checkbox"><br>
&lt;!field *check obj.field10 <span title='multilang label'>I_accept_the_terms</span>><br>
&lt;/div>
		</div>
		<div class="col-sm-4" dir="ltr">
<div class="checkbox"><!field *check obj.check I_accept_the_terms></div>
		</div>
		<div class="col-sm-6 text-muted small">
Mandatory check button with translated label
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field radio(<span title='numeric value'>1</span>) obj.radio0 <span title='multilang label'>one</span>><br>&lt;!field radio(<span title='numeric value'>2</span>) obj.radio0 <span title='multilang label'>two</span>>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field radio(1) obj.radio0 one> <!field radio(2) obj.radio0 two>
		</div>
		<div class="col-sm-6 text-muted small">
Note the automatic clickable labels
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field radio(<span title='string value'>'one'</span>) obj.radio1 <span title='multilang label'>one</span>><br>&lt;!field radio(<span title='string value'>'two'</span>) obj.radio1 <span title='multilang label'>two</span>>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field radio('one') obj.field12 one> <!field radio('two') obj.field12 two>
		</div>
		<div class="col-sm-6 text-muted small">
Similar, but return strings
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field num obj.num0>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field num obj.num0>
		</div>
		<div class="col-sm-6 text-muted small">
Decimal number input
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field num obj.num1(100,9000)>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field num(100,9000) obj.num1>
		</div>
		<div class="col-sm-6 text-muted small">
Number input with bound check and correction
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field file obj.file>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field file obj.file>
		</div>
		<div class="col-sm-6 text-muted small">
File upload
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field color obj.color>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field color obj.color>
		</div>
		<div class="col-sm-6 text-muted small">
Color picker
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field something obj.fieldX>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field something obj.fieldX>
		</div>
		<div class="col-sm-6 text-muted small">
A warning shown if no AddOn found for the specified field.
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field update> &lt;!field update Save>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field update> <!field update Save>
		</div>
		<div class="col-sm-6 text-muted small">
Form post with more buttons
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field button>
		</div>
		<div class="col-sm-4" dir="ltr">
<!field button>
		</div>
		<div class="col-sm-6 text-muted small">
HTML5 button with javascript handler
		</div>
	</div>

  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Library test")></b></div>

  <div class="panel-body">
	<div class="row">
		<div class="col-sm-2">
&lt;!field setsel obj.setsel core.addon()>
		</div>
		<div class="col-sm-6" dir="ltr">
<!field setsel obj.setsel core.addon()>
		</div>
		<div class="col-sm-4 text-muted small">
Select multiple values from a set. Drag'n'drop elements and reaarange them as you wish.
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field imglist obj.imglist crousel>
		</div>
		<div class="col-sm-6" dir="ltr">
<!field imglist obj.imglist crousel>
		</div>
		<div class="col-sm-4 text-muted small">
Select images for crousel, rearrange them as you like.
<!cms imglist(128) crousel>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field imglist obj.doclist legal>
		</div>
		<div class="col-sm-6" dir="ltr">
<!field doclist obj.doclist legal>
		</div>
		<div class="col-sm-4 text-muted small">
<!cms doc attach>Select documents for attachments, rearrange them as you like.
<!cms doclist legal>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
&lt;!field wyswyg obj.wyswyg>
		</div>
		<div class="col-sm-6" dir="ltr">
<!field wyswyg obj.wyswyg>
		</div>
		<div class="col-sm-4 text-muted small">
What You See is What You Get html editor
		</div>
	</div>

	<div class="row">
		<div class="col-sm-2">
		</div>
		<div class="col-sm-4" dir="ltr">
		</div>
		<div class="col-sm-6 text-muted small">
		</div>
	</div>

  </div>
</div>
</form>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Environment dump")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-lg-6">
<!dump _SERVER>
		</div>
		<div class="col-lg-6">
<!dump _SESSION>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-6">
<!dump core>
		</div>
		<div class="col-lg-6">
<!dump array_reverse(get_declared_classes())>
		</div>
	</div>
  </div>
</div>

<br><br>
</div>
