<!--
 @file vendor/phppe/Core/views/index.tpl
 @author bzt
 @date 1 Jan 2016
 @brief Self test view for PHPPE Core
-->
<style type='text/css' scoped>
@import url('http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
DIV.panel DIV.row:nth-of-type(even) { background:#f8f8f8; }
DIV.dump PRE { overflow:auto;max-height:300px; }
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
<!if core.lib('ds').primary><span class="text-success"><!=core.lib('ds').primary></span>
<!else><span class="text-info">files</span><!/if>
		</div>
		<div class="col-sm-6 text-muted small">
<!if core.lib('ds').primary>Primary datasource<!else>Local files only in <i>data</i> directory<!/if>
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

  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Input test")></b></div>
  <div class="panel-body">

  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Library test")></b></div>
  <div class="panel-body">

  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading"><b><!=L("Environment dump")></b></div>
  <div class="panel-body">
	<div class="row">
		<div class="col-sm-6">
<!dump _SERVER>
		</div>
		<div class="col-sm-6">
<!dump _SESSION>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
<!dump core>
		</div>
		<div class="col-sm-6">
<!dump array_reverse(get_declared_classes())>
		</div>
	</div>
  </div>
</div>

<br><br>
</div>
