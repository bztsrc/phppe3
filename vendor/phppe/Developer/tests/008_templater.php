<?php
use PHPPE\Core as PHPPE;

//! non existsent template should return empty string
echo("Empty template: ");
if( PHPPE::_gt("nonexistenttpl") != "" || PHPPE::template("nonexistenttpl") != "" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

//! raw template is not parsed
echo("Load raw template: ");
if( PHPPE::_gt("test1") != "aaa\n<!include test2>\nbbb\n" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

//! load and also parse the same template
echo("Load and parse template: ");
$a=PHPPE::template("test1");
if( !preg_match("|^aaaccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccd".
	"ccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccdccd.*?TOOMNY.*?dde\ndde\ndde\n".
	"dde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\n".
	"dde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\n".
	"dde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\ndde\nbbb\n|ims",$a ) ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

//! template name from application property (variable)
echo("Load dynamic template: ");
if( PHPPE::_t("<!include dynamictemplate>") != "dynamic name" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("Template parser: ");
if( PHPPE::_t("<!--not removed-->test1") != "<!--not removed-->test1" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("app tag: ");
if( PHPPE::_t("<!app>") != "<!app>" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("expression: ");
if( PHPPE::_t("<!=core.now>") != PHPPE::$core->now ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("template tag simple: ");
if( PHPPE::_t("<!template>aaa<!/template>") != "aaa" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("template tag re-entrant: ");
if( PHPPE::_t("<!template><%=core.now><!/template>") != PHPPE::$core->now ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("foreach tag scalar array: ");
if( PHPPE::_t("<!foreach test_arr><!=KEY><!=IDX><!=ODD><!=VALUE><!/foreach>") != "011a120b231c" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("foreach tag assoc array: ");
if( PHPPE::_t("<!foreach test_arr2><!=A><!/foreach>") != "abc" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("nested foreach tag: ");
if( PHPPE::_t("<!foreach test_arr><!foreach test_arr2><!=parent.KEY><!=A><!/foreach><!/foreach>") != "0a0b0c1a1b1c2a2b2c" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("if tag true: ");
if( PHPPE::_t("<!if true>A<!else>B<!/if>") != "A" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("if tag false: ");
if( PHPPE::_t("<!if false>A<!else>B<!/if>") != "B" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("mixed foreach/if tags: ");
if( PHPPE::_t("<!foreach test_arr><!if ODD>A<!else>B<!/if><!/foreach>") != "ABA" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("form tag: ");

if( !preg_match("|<form name='a' action='[^']+' method='post' enctype='multipart/form-data'><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
  PHPPE::_t("<!form a>")) ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("form tag with action: ");
if( !preg_match("|<form name='a' action='([^']+)' method='post' enctype='multipart/form-data'><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
  PHPPE::_t("<!form a b/c>"), $m) || substr($m[1],-4)!="b/c/") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("form tag with onsubmit: ");
if( !preg_match("|<form name='a' action='[^']+' method='post' enctype='multipart/form-data' onsubmit=\"d\(\)\"><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
  PHPPE::_t("<!form a - d()>")) ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("form tag with action and onsubmit: ");
if( !preg_match("|<form name='a' action='([^']+)' method='post' enctype='multipart/form-data' onsubmit=\"d\(\)\"><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
  PHPPE::_t("<!form a b/c d()>"), $m) || substr($m[1],-4)!="b/c/") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

PHPPE::$l['dateformat']="Y-m-d";
PHPPE::$l['testdate']="2001-02-03 04:05:06";
date_default_timezone_set( "UTC" );

echo("date and time with string: ");
if( PHPPE::_t('<!date L("testdate")>') != "2001-02-03" ||
	PHPPE::_t('<!time L("testdate")>') != "2001-02-03 04:05:06") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("date and time with timestamp: ");
if( PHPPE::_t("<!date 1>") != "1970-01-01" || !preg_match("/1970-01-01 [0-9]+:00:01/",PHPPE::_t("<!time 1>"))) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

PHPPE::$l['min']="m";
PHPPE::$l['mins']="ms";
PHPPE::$l['hour']="h";
PHPPE::$l['hours']="hs";
PHPPE::$l['day']="d";
PHPPE::$l['days']="ds";
echo("difftime tag: ");
if( PHPPE::_t("<!difftime 1>") != "0 m" || PHPPE::_t("<!difftime 60>") != "1 m" || PHPPE::_t("<!difftime 120>") != "2 ms"
 || PHPPE::_t("<!difftime 3600>") != "1 h" || PHPPE::_t("<!difftime 3660>") != "1 h, 1 m" || PHPPE::_t("<!difftime 3660*2>") != "2 hs, 2 ms"
 || PHPPE::_t("<!difftime 3600*24>") != "1 d" || PHPPE::_t("<!difftime 3600*48>") != "2 ds" || PHPPE::_t("<!difftime 3600*36>") != "1 d"
 || PHPPE::_t("<!difftime -120>") != "- 2 ms" ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

$r = PHPPE::$core->runlevel;

PHPPE::$core->runlevel = 0;
echo("dump tag runlevel 0 (prod): ");
if( PHPPE::_t("<!dump test_arr>") != "") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

PHPPE::$core->runlevel = 1;
echo("dump tag runlevel 1 (test): ");
if( PHPPE::_t("<!dump test_arr>") != "<b style='font:monospace;'>test_arr:</b><pre>Array
(
    [0] =&gt; a
    [1] =&gt; b
    [2] =&gt; c
)
</pre>") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

PHPPE::$core->runlevel = 2;
echo("dump tag runlevel 2 (devl): ");
if( !strpos(PHPPE::_t("<!dump test_arr>"), "string") ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

PHPPE::$core->runlevel = $r;

$_SESSION['pe_c']=$_SESSION['pe_e']=false;
echo("var tag (pe_c=0,pe_e=0,show): ");
if( PHPPE::_t("<!var test1 test>") != "show1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("widget tag (pe_c=0,pe_e=0,show): ");
if( PHPPE::_t("<!widget test1 test>") != "show1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("field tag (pe_c=0,pe_e=0,edit): ");
if( PHPPE::_t("<!field test1 test>") != "edit1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

$_SESSION['pe_c']=true;
echo("var tag (pe_c=1,pe_e=0,show): ");
if( PHPPE::_t("<!var test1 test>") != "show1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("widget tag (pe_c=1,pe_e=0,edit): ");
if( PHPPE::_t("<!widget test1 test>") != "edit1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("field tag (pe_c=1,pe_e=0,edit): ");
if( PHPPE::_t("<!field test1 test>") != "edit1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

$_SESSION['pe_c']=false;
$_SESSION['pe_e']=true;
echo("var tag (pe_c=0,pe_e=1,edit): ");
if( PHPPE::_t("<!var test1 test>") != "edit1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("widget tag (pe_c=0,pe_e=1,show): ");
if( PHPPE::_t("<!widget test1 test>") != "show1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("field tag (pe_c=0,pe_e=1,edit): ");
if( PHPPE::_t("<!field test1 test>") != "edit1") {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

$_SESSION['pe_c']=$_SESSION['pe_e']=false;
echo("var tag (no acl): ");
if( PHPPE::_t("<!var @noacl test1 test>") != (PHPPE::$user->id==-1?"show1":"") ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

echo("field tag (no acl): ");
if( PHPPE::_t("<!field @noacl test1 test>") != (PHPPE::$user->id==-1?"edit1":"") ) {
	echo("Failed!\n");
	return false;
} else echo("OK\n");

return true;
?>