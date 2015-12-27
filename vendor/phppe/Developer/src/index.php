<?php
/*
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2015 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 *  PHPPE Core - Use as is
 */
/**
 * @file public/index.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief PHPPE micro-framework's Core
 *
 * This is the nicely formatted and commented source version of PHPPE Core
 * !!! IMPORTANT !!! If you want to keep forward compatibility, don't touch!
 * @see http://phppe.org/src.php.txt
 */
namespace PHPPE {
	define("VERSION", "3.0.0");

/*** class definitions ***/

/**
 * Add-On prototype
 */
	class AddOn
	{
		public $args;				//!< arguments in pharenthesis after type
		public $name;				//!< instance name
		public $fld;				//!< field name
		public $value;				//!< object field's value
		public $attrs;				//!< attributes, everything after the name in tag
		public $css;				//!< css class to use, input or reqinput, occasionally errinput added
/**
 * constructor, do not try to override, use init() instead
 *
 * @param arguments, listed with pharenthesis after type in templates
 * @param name of the add-on
 * @param reference of object field
 * @param attributes, listed after field name in templates
 * @param required field flag
 * @return PHPHE3\AddOn instance
 */
		final function __construct($a, $n, &$v, $t = [], $r = 0)
		{
			//! save arguments, name and attributes
			$this->args = $a;
			$this->name = $n;
			$this->fld = s($n, ".", "_");
			$this->value = $v;
			$this->attrs = $t;
			//! css class name reinput for mandatory fields
			$this->css = ((! empty($r) ? "req" : "") . "input") . (Core::isError($n) ? " errinput" : "");
		}
/**
 * init method is called when needed but only once per page generation
 * constructor may be called several times depending on the view used
 */
		//! function init($cfgarray)
		//! {
		//!   call \PHPPE\Core::addon() and specify your Add-On's details
		//!   \PHPPE\Core::jslib() to load javascripts and
		//!   \PHPPE\Core::css() for style sheets here
		//! }
/**
 * field input or widget configuration form
 * @return string output
 */
		//! function edit() {return "";}
/**
 * display a field's value or show widget face
 * @return string output
 */
		function show()
		{
			return "";
		}
/**
 * value validator, returns boolean and a failure reason
 *
 * @param name of value to valudate, for error reporting
 * @param reference to value
 * @param arguments
 * @param attributes
 * @return array(boolean,error message) if the first value is true, it's valid
 */
		//! static function validate( $name, &$value,$args,$attrs )
		//! {
		//!	return array(true, "Dummy validator that always pass");
		//! }
	}

/**
 * Filter prototype
 */
	class Filter
	{
	}

/**
 * Application prototype
 */
	class App
	{
	}

/**
 * Application controller prototype
 */
	class Ctrl extends App
	{
	}

/**
 * Model that supports Object Relational Mapping
 */
	class Model
	{
		public $id;
		public $name;
		protected static $_table;
/**
 * Find objects of the same kind in database
 *
 * @param search phrase
 * @param where clause with placeholders
 * @param order by
 * @return array of associative arrays
 */
		final function find($s = [], $w = "", $o = "")
		{
			if(empty(static ::$_table))
				throw new \Exception("no _table");
			//get the records from current datasource
			return Core::query("*", static ::$_table, $w ? $w : ($s ? "id=?" : ""), "", $o, 0, 0, a($s) ? $s : [ $s ]);
		}
/**
 * Load, reload or find a record in database and load result into this object
 *
 * @param id of the object to load, or (if second argument given) search phrase
 * @param where clause with placeholders
 * @return true on success
 */
		final function load($i = 0, $w = "", $o = "")
		{
			if(empty(static ::$_table))
				throw new \Exception("no _table");
			//get the record from current datasource
			$r = Core::fetch("*", static ::$_table, $w ? $w : "id=?", "", $o, a($i) ? $i : [ $i ? $i : $this->id ]);
			//update property values. FETCH_INTO not exactly does what we want
			if(! empty($r)) {
				foreach($this as $k => $v)
					if($k[ 0 ] != '_')
						$this->$k = is_string($r[ $k ]) && ($r[ $k ][ 0 ] == '{' || $r[ $k ][ 0 ] == '[') ? jd($r[ $k ]) : $r[ $k ];
				return true;
			}
			return false;
		}
/**
 * Save the current object into database. May also alter $id property.
 *
 * @return true on success
 */
		final function save($f = 0)
		{
			if(empty(static ::$_table))
				throw new \Exception("no _table");
			$d = Core::db();
			if(empty($d))
				throw new \Exception("no ds");
			//build the arguments array
			$a = [];
			foreach($this as $k => $v)
				if($k[ 0 ] != '_' && ($f || $k != 'id') && $k != 'created')
					$a[ $k ] = is_scalar($v) ? $v : json_encode($v);
			if(! Core::exec(($this->id && ! $f ? "UPDATE " . static ::$_table . " SET " . implode("=?,", array_keys($a)) . "=? WHERE id=" . $d->quote($this->id) : "INSERT INTO " . static ::$_table . " (" . implode(",", array_keys($a)) . ") VALUES (?" . str_repeat(",?", count($a) - 1) . ")"), array_values($a)))
				return false;
			//save new id for inserts
			if(! $this->id)
				$this->id = $d->lastInsertId();
			//return id
			return $this->id;
		}
	}

/**
 * client class, this is used to store client's information
 */
	class Client
	{
		public $ip;				//!< remote ip address. Also valid if behind proxy or load balancer
		public $agent;				//!< client program
		public $user;				//!< user account (unix user on CLI, http auth user on web)
		public $tz;				//!< client's timezone
		public $lang;				//!< client's prefered language
		public $screen = [];			//!< screen dimensions
	}

/**
 * default user class, will be overriden by PHPPE Pack with Users class
 */
	class User extends Model
	{
		public $id = 0;				//!< only for Anonymous. Otherwise user id can be a string as well
		public $name = "Anonymous";		//!< user real name
		public $data = [];			//!< user preferences
		// protected static $_table = "users";	//! set table name. This should be in Users class!
		private $acl = [];			//!< Access Control List
		// private remote = [];			//!< remote server configuration, added run-time
/**
 * check access for an access control entry
 *
 * @param access control entry or list (pipe separated string or array)
 * @return boolean true or false
 */
		final function has($l)
		{
			//check if at least one of the ACE match
			foreach(a($l) ? $l : x("|", $l) as $a) {
				$a = r($a);
				//is user logged in?
				//is superadmin with bypass priviledge?
				if(! empty($this->id) && ($this->id == - 1 || $a == "loggedin" || ! empty($this->acl[ $a ]) || ! empty($this->acl[ "$a:" . self::$core->item ])))
					return 1;
			}
			return 0;
		}
/**
 * grant priviledge for a user
 *
 * @param access control entry or list (pipe separated string or array)
 */
		final function grant($l)
		{
			foreach(a($l) ? $l : x("|", $l) as $a) {
				$a = r($a);
				if(! empty($this->id) && ! empty($a))
					$this->acl[ $a ] = 1;
			}
		}
/**
 * drop privileges, specific access control entry or the whole access control list
 *
 * @param access control entry or list (pipe separated string or array) or empty for dropping all
 */
		final function clear($l = "")
		{
			if(empty($l))
				$this->acl = [];
			else
			{
				foreach(a($l) ? $l : x("|", $l) as $a) {
					$a = r($a);
					//! drop the ACE
					unset($this->acl[ $a ]);
					//! drop item specific ACEs
					foreach($this->acl as $k => $v)
						if(z($k, 0, u($a) + 1) == $a . ":")
							unset($this->acl[ $k ]);
				}
			}
		}
	}

/****** PHPPE Core ******/
	//! required by formatter (fmt.php)
	define('M', "vendor/");
	define('N', M . "phppe/");
	define('P', N . "core/");
	define('PE', ".php");
	define('C', "\\PHPPE\\");
	define('A', C . "AddOn\\");
	define('I', "index");

/**
 * this is the heart of PHPPE, the class of \PHPPE\Core::$core
 */
	class Core
	{
		private $id;				//!< magic, 'PHPPE'+VERSION
		public $base;				//!< base url
		public $site;				//!< title of the site
		public $runlevel = 2;			//!< 0-production,1-test,2-development,3-debug
		public $app;				//!< main page generator controller
		public $action;				//!< main subpage generator action (extends page)
		public $item;				//!< item to work with, usually an id
		public $form;				//!< name of the submitted form
		public $now;				//!< current server timestamp, from primary datasource if available
		public $syslog = false;			//!< send logs to syslog
		public $timeout;			//!< session timeout
		public $output;				//!< templater output header and footer selector
		public $template;			//!< templater app's template to use
		public $mailer;				//!< mailer backend (smtp relay url)
		public $cache;				//!< memcache url
		public $cachettl = 600;			//!< whole output cache ttl in sec
		public static $core;			//!< self reference, phppe system
		public static $user;			//!< user layer
		public static $client;			//!< client data
		public static $l = [];			//!< language translations
		public static $fm;			//!< file max size
		private $meta = [];			//!< meta tags
		private $try;				//!< none-zero for update transaction starts, 1 up to 9
		private $started;			//!< script start time in msec, float
		private $error;				//!< error messages array
		private $libs;				//!< list of initialized modules and libraries
		private $addons;			//!< list of initialized widgets
		private $disabled;			//!< list of disabled modules and libraries
		private $js;				//!< templater include javascript functions
		private $jslib;				//!< templater include js libraries
		private $css;				//!< templater include stylesheets
		private $menu;				//!< system menu, populated by initialized modules
		private static $r;			//!< url routes
		private static $n;			//!< templater nested level
		private static $c;			//!< templater control structures context
		private static $o;			//!< templater objects
		private static $p;			//!< templater default path for views
		private static $mc;			//!< cache object
		private static $tc;			//!< try button counter
		private static $db;			//!< database layer
		private static $s;			//!< data source selector
		private static $b;			//!< time consumed by data source queries (bill for db)
		private static $w;			//!< boolean, true if REQUEST_METHOD not empty
		private static $v;			//!< validator data
		static $g;				//!< posix group
/**
 * constructor. If you pass true as argument, it will build up PHPPE environment,
 * but won't run your application. For that you'll need to call \PHPPE\Core::$core->run()
 *
 * @param true is called as a library
 * @return \PHPPE\Core instance
 */
		function __construct($islib = true)
		{
			//! server time is calculated with (this - http request arrive time)
			$this->started = microtime(1);
			//! set self reference
			$core = self::$core = &$this;
			//! patch php, set defaults
			set_exception_handler(function(\Exception $e)
			{
				self::log('C', "Exception: " . $e->getMessage() . (empty(self::$core->trace) ? "" : "\n\t" . s($e->getTraceAsString(), "\n", "\n\t"))); 

			});
			ini_set("error_log", n(__DIR__) . "/" . P . "log/php.log");
			ini_set("log_errors", 1);
			//! php version check
			if(version_compare(PHP_VERSION, "5.5") < 0)
				self::log("C", "PHP 5.5.0 required, found " . PHP_VERSION);
			ini_set("file_uploads", 1);
			ini_set("upload_tmp_dir", n(__DIR__) . "/.tmp");
			ini_set("uploadprogress.file.filename_template", n(__DIR__) . "/.tmp/upd_%s.txt");
			if(y("mb_internal_encoding"))
				mb_internal_encoding("utf-8");
			//! fix slashes in request
			if(get_magic_quotes_gpc()) {
				foreach($_REQUEST as $k => $v)
					if(is_string($v))
						$_REQUEST[ $k ] = stripslashes($v);
				elseif(a($v))
					foreach($v as $K => $V)
						if(is_string($V))
							$_REQUEST[ $k ][ $K ] = stripslashes($V);
			}
			//! self check
			//! this is updated by the php formatter (fmt.php)
			//$c=__FILE__;if(filesize($c)!=65535||'eac7bb45f675d1f73b1ae0b44f14c45a141cd8c9'!=sha1(pr("/\'([^\']+)\'\!\=sha1/","''!=sha1",g($c))))self::log("C","Corrupted ".m($c));
			//!
			//! set default working directory to projectroot
			chdir(n(__DIR__));
			//! initialize PHPPE environment
			$S = $_SERVER;
			$R = $_REQUEST;
			//! load configuration
			$c = P . "config" . PE;
			$this->disabled = [];
			if(f($c))
				require_once($c);
			//! range checks
			$this->id = "PHPPE" . VERSION;
			if($this->runlevel < 0 || $this->runlevel > 3)
				$this->runlevel = 0;
			if($this->timeout < 60)
				$this->timeout = 7 * 24 * 3600;
			if($this->cachettl < 10)
				$this->cachettl = 10;
			//! functions allowed in view expressions
			if(! empty($this->allowed) && ! a($this->allowed))
				$this->allowed = x(",", $this->allowed);
			//! disabled extensions
			if(! a($this->disabled))
				$this->disabled = x(",", $this->disabled);
			//! patch php. this must be done _after_ config loaded
			ini_set("display_errors", $this->runlevel > 1 ? 1 : 0);
			$this->now = time();
			$this->error = $this->js = $this->jslib = $this->css = $this->libs = [];
			$this->sec = (t(getenv("HTTPS")) == "on") ? 1 : 0;
			//! set up some default values
			self::$w = isset($S[ 'REQUEST_METHOD' ]) ? 1 : 0;
			$this->output = self::$w ? "html" : "tty";
			$this->try = self::$tc = 0;
			//! construct base href
			$c = $S[ 'SCRIPT_NAME' ];
			$C = n($c);
			if($C != "/")
				$C .= "/";
			$d = "SERVER_NAME";
			$this->base = (! empty($this->base) ? $this->base : (! empty($S[ $d ]) ? $S[ $d ] : "localhost")) . ($C[ 0 ] != "/" ? "/" : "") . $C;
			//! get application, action and item
			list($d) = x("?", @$S[ 'REQUEST_URI' ]);
			foreach([ $c, n($c) ] as $C)
				if($C != "/" && z($d, 0, u($C)) == $C) {
					$d = w($d, u($C));
					break;
				}
			$D = "--dump";
			$A = "argv";
			$d = x("/", ! empty($d) ? $d : "//");
			foreach([ 1 => "app", 2 => "action", 3 => "item" ] as $c => $v)
				$this->$v = ! empty($d[ $c ]) ? t($d[ $c ]) : (! empty($R[ $v ]) ? r(t($R[ $v ])) : (! self::$w && ! empty($S[ $A ][ $c ]) && $S[ $A ][ $c ] != $D ? r(t($S[ $A ][ $c ])) : ($c < 3 ? ($c == 1 ? I : "action") : "")));
			//! a few basic security check
			$c = $this->app . "_" . $this->action;
			if(strpos($c, "..") !== false || strpos($c, "/") !== false || w($this->app, - 4) == PE || w($this->action, - 4) == PE)
				$this->redirect("403");
			//! default template
			$this->template = $c;
			//! calculate upload max file size
			$c = self::si("post_max_size");
			$d = self::si("upload_max_filesize");
			$v = self::si("memory_limit");
			if($c > $d && $d)
				$c = $d;
			self::$fm = ($c > $v && $v ? $v : $c);
			//! check arguments
			if(! self::$w && ! empty($S[ $A ][ 1 ])) {
				if(ia("--version", $S[ $A ]))
					die(VERSION . "\n");
				if(ia("--help", $S[ $A ]))
					die("PHP Portal Engine " . VERSION . ", LGPL 2015 bzt\n\nphp " . m(__FILE__) . " (cmd | [application [action [item]]] ) [ $D ]\n\nCommands:\n --version\n --diag [--gid=x]\n");
			}
			//! session restore may require models, so we have to
			//! load all classes *before* session_start()
			//
			//! load function extensions, password checker and asset minifier, dynamic content handler and config registry
			$v = "libs/pass" . PE;
			$c = "app/$v";
			if(f($c))
				io($c);
			else
			{
				$c = P . $v;
				io($c);
			}
			$c = P . "libs/minify" . PE;
			if(empty($this->nominify))
				io($c);
			if(! y(C . "minify")) {
				function minify($t, $T)
				{
					return $t;
				}
			}
			//! PHP Composer autoload support
			$c = M . "autoload" . PE;
			if(f($c))
				@io($c);
			//! autoload libraries in SysV style using this pattern:
			//!   vendor/phppe/X/[0-9][0-9]_X.php
			//! levels:
			//!   00: reserved for Core
			//!   01: reserved for Pack extensions
			//!   02-49: library extensions (typically controllerless and independent)
			//!   50-98: more complex extensions
			//!   99: reserved for project's main application
			self::$r = [];
			$d = @glob(N . "*/*_*" . PE, GLOB_NOSORT);
			//! we are aware that $d can be empty
			usort($d, function($a, $b)
			{
				return strcmp(m($a), m($b)); 

			});
			foreach($d as $f) {
				//!extra check on glob's output - first part must be numeric, second must match directory name
				if(p("/([^\/]+)\/([0-9]{2}_([^\.]+)\.php)$/", $f, $m) && $m[ 1 ] == $m[ 3 ] && ! ia($m[ 3 ], $this->disabled)) {
					$c = C . $m[ 1 ];
					//we don't have session pe_l yet, don't use langInit
					io($f);
					if(ce($c) && t($c) != t("\\" . __CLASS__)) {
						$C = new $c();
						//don't allow libraries to overwrite DataSource layer
						if($m[ 1 ] != "DS" && empty($this->libs[ $m[ 1 ] ]))
							$this->libs[ $m[ 1 ] ] = $C;
					}
				}
			}
			//! detect bootstrap type
			if(! self::$w && ((! f(P . "config" . PE) && ! $islib) || (! empty($S[ $A ][ 1 ]) && $S[ $A ][ 1 ] == "--diag")))
				$this->bootdiag();
			else
			{
				//! normal bootsrap
				$this->bootstrap();
				//! if not included as a library, run application
				if(! $islib)
					$this->run();
			}
		}
/**
 * run diagnostics and try to fix errors
 */
		private function bootdiag()
		{
			//		ini_set( "display_errors", 1 );
			header("Content-Type:text/plain");
			//! extensions checks and webserver group id
			if(! extension_loaded("posix") && y("dl"))
				@dl((PHP_SHLIB_SUFFIX == "dll" ? "php_" : "") . "posix." . PHP_SHLIB_SUFFIX);
			if(! empty($_SERVER[ 'argv' ][ 2 ]) && z($_SERVER[ 'argv' ][ 2 ], 0, 6) == "--gid=")
				$g[ "g" ] = intval(w($_SERVER[ 'argv' ][ 2 ], 6));
			elseif(y("posix_getpwnam"))
				foreach([ "www", "_www", "www-data", "http", "httpd", "apache", "nginx" ] as $n) {
					$g = posix_getpwnam($n);
					if(! empty($g[ "gid" ])) {
						$g[ "g" ] = $g[ "gid" ];
						break;
					}
				}
			if(empty($g[ "g" ]))
				self::$g = 33;
			else 
				self::$g = $g[ "g" ];
			$U = fileowner(__FILE__);
			if($this->runlevel)
				echo("DIAG-I: uid $U gid " . self::$g . "\n");
			//! helper function to create files
			function i($c, $r, $f = 0, $a = 0640)
			{
				if(! f($c) || $f) {
					if(! f($c))
						echo("DIAG-A: $c\n");
					file_put_contents($c, $r);
				}
				if(f($c) && (! @chgrp($c, \PHPPE\Core::$g) || ! @chown($c, fileowner(__FILE__))))
					echo("DIAG-E: chown/chgrp $c\n");
				return ! @chmod($c, $a);
			}
			$E = "";
			$C = 0750;
			$W = 0775;
			if(y("posix_getuid") && posix_getuid() != 0)
				echo("DIAG-W: not root or no php-posix, chown/chgrp may fail!\n");
			//! create directory structure
			$o = umask(0);
			//! hide errors here, target may not exists or the symlink may be already there
			@symlink(N . "core", "phppe");
			$D = [ ".tmp" => $W, "data" => $W, "app" => 0, M => 0755, M . "bin" => 0, N => 0, N . "core" => 0, P . "log" => $W, P . "views" => 0, P . "ctrl" => 0, P . "lang" => 0, P . "libs" => 0, P . "out" => 0, P . "sql" => 0, "public/images" => 0, "public/css" => 0, "public/js" => 0, "app/ctrl" => 0, "app/lang" => 0, "app/libs" => 0, "app/views" => 0 ];
			$A = [ "*", "*/*", "*/*/*", "*/*/*/*", "*/*/*/*/*" ];
			foreach($A as $v)
				$D += array_fill_keys(@glob(M . $v), 0);
			foreach($D as $d => $p) {
				if(is_file($d)) {
					$x = substr($d, 0, 4) == ".tmp" || substr($d, 0, 4) == "data" || substr($d, 0, 21) == P . "log";
					$P = fileperms($d) &0777;
					$p = $x ? 0660 : 0640;
				}
				else
				{
					if(! $p)
						$p = $C;
					if(! is_dir($d) && ! is_file($d)) {
						echo("DIAG-A: $d\n");
						if(! mkdir($d, $p))
							self::log("C", "creating $d", "diag");
					}
					$P = fileperms($d) &0777;
				}
				if($P != $p) {
					$E .= sprintf("\t%03o?\t%03o ", $P, $p) . "$d\n";
					@chmod($d, $p);
				}
				if(! @chgrp($d, self::$g) || ! @chown($d, $U))
					echo("DIAG-E: chown/chgrp $d\n");
			}
			//! hide errors here, symlink may be already there
			@symlink("../../app", N . "app");
			foreach([ "images", "css", "js" ] as $v) {
				if(! f("app/$v"))
					echo("DIAG-A: app/$v\n");
				@symlink("../public/$v", "app/$v");
			}
			//! create files
			umask(0027);
			i("app/config" . PE, "");
			/*
		$c = "public/.htaccess";
		if (@i( $c,"RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^(.*)\$ index.php/\$1\n", 1 ))
		{
			$p = ( fileperms( $c ) &0777 );
			if( ( $p &0222 ) != 0 )$E .= sprintf( "\t%o?\t%4o ", $p,$p &0555 ) . "$c\n";
		}
*/
			i("public/favicon.ico", "");
			i(P . "config" . PE, "");
			$U = "http://phppe.org/";
			$D = P . "views/";
			$e = ".tpl";
			$c = "<!dump core.req2arr('obj')>";
			i($D . "403$e", "<h1>403</h1><!=L('Access denied')>");
			i($D . "404$e", "<h1>404</h1><!=L('Not found')>: <b><!=core.app></b>");
			i($D . "frame$e", "<div id='content'><!app></div>");
			i($D . I . $e, "<h1>PHPPE works!</h1>Next step: install <a href='" . $U . "phppe3.html#install:1956' target='_new'>PHPPE Pack</a>.<br/><br/><!if core.istry()><div style='display:none;'>$c</div><!/if><div style='background:#F0F0F0;padding:3px;'><b>Test form</b></div><!form obj>Text<!field text(6) obj.f0> Pass<!field pass(6) obj.f1> Num(100..999)<!field *num(6,6,100,999) obj.f2><!field check obj.f3 Check>  File<!field file obj.f4> <!field cancel>  <!field submit></form><table width='100%'><tr><td valign='top' width='50%'><!dump _REQUEST><!dump _FILES></td><td valign='top'>$c</td></tr></table>\n");
			i($D . "login$e", "<!form login><!field text id><!field pass pass><!field submit></form>");
			i("composer.json", "{\n\t\"name\":\"phppe3\",\n\t\"version\":\"1.0.0\",\n\t\"keywords\":[\"phppe3\",\"\"],\n\t\"license\":[\"LGPL-3.0+\"],\n\n\t\"type\":\"project\",\n\t\"repositories\":[\n\t\t{\"type\":\"composer\",\"url\":\"$U\"}\n\t],\n\t\"require\":{\"phppe\":\"3.*\"},\n\n\t\"scripts\":{\"post-update-cmd\":\"sudo php public/index.php --diag\"}\n}\n");
			i(".gitignore", ".tmp\nphppe\nvendor\n");
			if($E)
				self::log("E", "Wrong permissions:\n$E", "diag");
			//! apply sql updates
			$D = [];
			$c = "/sql/upd_*.";
			$d = self::lib("ds");
			foreach($A as $v)
				foreach($d ? [ "", $d->primary ] : [ "" ] as $s)
					$D += array_fill_keys(@glob(M . $v . $c . $s . ".sql"), 0);
			if(count($D))
				echo("DIAG-I: db update\n");
			foreach($D as $f => $v) {
				//get sql commands from file
				$s = x(";", g($f));
				@unlink($f);
				//execute one by one
				foreach($s as $q)
					self::exec($q);
			}
			//! *** DIAG Event ***
			$this->_eh("diag");
			umask($o);
			die("DIAG-I: OK\n");
		}
/**
 * create PHPPE environment, normal bootstrap
 *  step 1: autoload classes
 *  step 2: start user session
 *  step 3: detect client
 *  step 4: initialize modules
 *  step 5: initialize cache
 *  step 6: initialize primary data source
 */
		private function bootstrap()
		{
			//! normal bootstrap
			$S = $_SERVER;
			$R = $_REQUEST;
			//! start user session
			session_name(! empty($this->sessionvar) ? $this->sessionvar : "pe_sid");
			session_start();
			if(ini_get("session.use_cookies"))
				setcookie(session_name(), session_id(), time() + $this->timeout, "/", $S[ "SERVER_NAME" ], ! empty($this->secsession) ? 1 : 0, 1);
			//! *** User ***
			$c = C . "User";
			$u = $c . "s";
			if(! ce($u) || ! is_subclass_of($u, $c))
				$u = $c;
			$L = "pe_u";
			if(! empty($_SESSION[ $L ]) && is_object($_SESSION[ $L ]))
				self::$user = $_SESSION[ $L ];
			else 
				self::$user = $_SESSION[ $L ] = new $u();
			//! *** Client ***
			self::$client = new Client;
			if(self::$w) {
				$L = 'HTTP_IF_MODIFIED_SINCE';
				if(! $this->runlevel && isset($S[ $L ]) && $S[ $L ] + $this->cachettl < $this->now) {
					header('HTTP/1.1 304 Not Modified');
					die;
				}
				//! handle cancel as early as possible
				if(isset($R[ "pe_cancel" ]))
					$this->redirect();
				//! destroy user session if requested
				if(isset($R[ 'clear' ])) {
					$u = $_SESSION[ $L ];
					$_SESSION = [];
					$_SESSION[ $L ] = $u;
					$this->redirect();
				}
				//! operation modes
				if(! empty(self::$user->id)) {
					foreach([ "edit", "conf" ] as $v) {
						if(isset($R[ $v ]) && self::$user->has($v)) {
							$_SESSION[ 'pe_' . z($v, 0, 1) ] = ! empty($R[ $v ]);
							$this->redirect();
						}
					}
				}
				//! detect browser's language, timezone and screen size
				$L = "pe_tz";
				if($this->app == I && empty($_SESSION[ $L ]) && ! isset($R[ 'nojs' ]) && empty($R[ 'cache' ])) {
					$c = L("Enable JavaScript");
					if(empty($R[ 'n' ])) {
						$_SESSION[ 'pe_n' ] = sha1(rand());
						$this->_r();
						$g = "getTimezoneOffset()";
						$d = "var d%=new Date();d%.setDate(1);d%.setMonth(@);d%=parseInt(d%.$g);";
						die("<html><script type='text/javascript'>var now=new Date();" . s($d, [ '%' => '1', '@' => '1' ]) . s($d, [ '%' => '2', '@' => '7' ]) . "txt=now.toString().replace(/[^\(]+\(([^\)]+)\)/,\"$1\");document.location.href=\"" . $_SESSION[ "pe_r" ] . (strpos($S[ 'REQUEST_URI' ], "?") === false ? "?" : "&") . "n=" . $_SESSION[ 'pe_n' ] . "&t=\"+(-now.$g*60)+\"&d=\"+(d1==d2||(d1<d2&&d1==parseInt(now.$g))||(d1>d2&&d2==parseInt(now.$g))?\"1\":\"0\")+\"&w=\"+screen.availWidth+\"&h=\"+screen.availHeight;</script>$c</html>");
					}
					elseif($R[ 'n' ] == $_SESSION[ 'pe_n' ]) {
						unset($_SESSION[ 'pe_n' ]);
						$_SESSION[ $L ] = timezone_name_from_abbr("", $R[ 't' ] + 0, $R[ 'd' ] + 0);
						$_SESSION[ 'pe_w' ] = floor($R[ 'w' ]);
						$_SESSION[ 'pe_h' ] = floor($R[ 'h' ]);
						$this->redirect();
					}
					else 
						die($c);
				}
				//! get client's real ip address
				$d = 'HTTP_X_FORWARDED_FOR';
				self::$client->ip = ! empty($S[ $d ]) ? $S[ $d ] : $S[ 'REMOTE_ADDR' ];
				self::$client->screen = ! empty($_SESSION[ 'pe_w' ]) ? [ $_SESSION[ 'pe_w' ], $_SESSION[ 'pe_h' ] ] : [ 0, 0 ];
				$d = 'HTTP_USER_AGENT';
				self::$client->agent = ! empty($S[ $d ]) ? $S[ $d ] : "browser";
				$d = 'PHP_AUTH_USER';
				self::$client->user = ! empty($S[ $d ]) ? $S[ $d ] : "";
			}
			else
			{
				//! defaults for CLI
				$T = getenv("TZ");
				$d = x("/", $T ? $T : @readlink("/etc/localtime"));
				$c = count($d);
				$_SESSION[ $L ] = $c > 1 ? $d[ $c - 2 ] . "/" . $d[ $c - 1 ] : "UTC";
				$c = exec("tput cols") + 0;
				$d = exec("tput lines") + 0;
				self::$client->ip = "CLI";
				self::$client->screen = [ $c < 1 ? 80 : $c, $d < 1 ? 25 : $d ];
				$d = getenv("TERM");
				self::$client->agent = ! empty($d) ? $d : "term";
				$d = getenv("USER");
				self::$client->user = ! empty($d) ? $d : "";
				$this->noframe = 1;
				//! change default application for scripts
				if($this->app == I)
					$this->app = "scripts";
			}
			//! set up client's timezone
			date_default_timezone_set(self::$client->tz = ! empty($_SESSION[ $L ]) ? $_SESSION[ $L ] : "UTC");
			//! set up client's prefered language
			//! only allow if language is defined in core or in app
			$L = 'pe_l';
			$a = "";
			$d = [];			
			//! get prefered language from browser or from environment
			if(empty($_SESSION[ $L ])) {
				$i = 'HTTP_ACCEPT_LANGUAGE';
				$d = x(",", s(! empty($S[ $i ]) ? $S[ $i ] : (getenv('LANG') || "en"), "/", ""));
			}
			if(! empty($R[ 'lang' ]))
				$d = [ s(r($R[ 'lang' ]), "/", "") ];
			foreach($d as $v) {
				list($a) = x(";", t($v));
				if(! empty($a) && (f(P . "lang/$a" . PE) || f("app/lang/$a" . PE))) {
					$_SESSION[ $L ] = $a;
					break;
				}
			}
			if(empty($_SESSION[ $L ]))
				$_SESSION[ $L ] = "en";
			self::$client->lang = $v = $_SESSION[ $L ];
			$i = x("_", s($v, "-", "_"));
			//! set PHP locale for the language
			setlocale(LC_ALL, t($i[ 0 ]) . "_" . k(! empty($i[ 1 ]) ? $i[ 1 ] : $i[ 0 ]) . ".UTF8");
			//! multilanguage support for JavaScript
			$C = "core";
			if(self::isInst($C)) {
				$this->jslib[ "$C.$v.js" ] = P . "js/$C.js" . PE;
				$this->css[ "$C.css" ] = P . "css/$C.css";
			}
			//! load core dictionary
			self::$l = [];
			LANG_INIT($C, $i);
			//! load autoloaded classes' dictionaries and initialize them
			//! *** INIT Event ***
			foreach($this->libs as $k => $v) {
				LANG_INIT($k, $i);
				$c = N . $k . "/config" . PE;
				if(q($v, "init") && ! $v->init(f($c) ? io($c) : []))
					unset($this->libs[ $k ]);
			}
			//! load application dictionary
			LANG_INIT("app", $i);
			//! initialize memory caching if configured
			if(! empty($this->cache)) {
				$C = "cache";
				//! try to load cache extension
				$c = P . "libs/$C" . PE;
				if(f($c))
					self::$mc = require_once($c);
				//! if it returned null, fallback to memcached
				if(! is_object(self::$mc)) {
					$M = "\\Mem$C";
					if(! ce($M))
						self::log('C', "no php-memcached", $C);
					//! unix file: "unix:/tmp/fifo", "host" or "host:port" otherwise
					$m = x(":", $this->$C);
					if($m[ 0 ] == "unix") {
						$p = 0;
						$h = $m[ 1 ];
					}
					else
					{
						$p = $m[ 1 ] + 0;
						$h = $m[ 0 ];
					}
					self::$mc = new $M;
					//self::$mc->addServer( $h, $p );
					//$s = @self::$mc->getExtendedStats(  );
					if(/*empty( $s[ $h . ( $p > 0 ? ":" . $p : "" ) ] ) || */ ! @self::$mc->pconnect($h, $p, 1)) {
						usleep(100);
						if(! @self::$mc->pconnect($h, $p, 1))
							self::$mc = null;
					}
				}
				//! let rest of the world know about us
				self::lib("mc", L(ucfirst($C)), "", self::$mc);
			}
			//! initialize primary datasource if configured
			if(! empty($this->db)) {
				//! replace string $this->db with an array of pdo object
				@self::db($this->db);
				//! get current timestamp from primary datasoure
				//! this will override time() in $core->now with
				//! a time in database server's timezone
				$d = "CURRENT_TIMESTAMP";
				try {
					$t = @strtotime(@self::field($d));
					if($t > 0)
						$this->now = $t;
				}
				catch(\Exception $e) {
				}
			}
		}
/**
 * execute a PHPPE application
 *  step 1: serve built-in assets and cache requests
 *  step 2: serve assets under vendor (fake apps "css","js","images")
 *  step 3: url routing
 *  step 4: load application class
 *  step 5: look for action handler
 *  step 6: generate main content with templater or get it from cache
 *  step 7: output header
 *  step 8: output frame / main content
 *  step 9: output footer
 *
 * @param application name, if not specified, url routing will choose
 * @param action name, if not specified, default action routing will apply
 */
		function run($n = "", $ac = "")
		{
			//! get path from request uri (cut off script name)
			list($c) = x("?", @$_SERVER[ 'REQUEST_URI' ]);
			$s = $_SERVER[ 'SCRIPT_NAME' ];
			$u = w($c, (z($c, 0, u($s)) == $s ? u($s) : u(n($s))) + 1);
			if($u[ 0 ] == "/")
				$u = w($c, 1);
			if($u[ u($u) - 1 ] == "/")
				$u = z($u, 0, u($u) - 1);
			//! built-in blobs - referenced as cached objects
			$C = "cache";
			$a = "action";
			if(! empty($_GET[ $C ])) {
				$d = r($_GET[ $C ]);
				switch($d) {
					case "logo" :
					c("image/png");
					$c = P . "images/.phppe";
					die(f($c) ? g($c) : base64_decode("R0lGODlhKgAYAMIHAAACAAcAABYAAygBDD4BEFwAGGoBGwWYISH5BAEKAAcALAAAAAAqABgAAAOxeLrcCsDJSSkIoertYOSgBmXh5p3MiT4qJGIw9h3BFZP0LVceU0c91sy1uMwkwQfmYEzhiCwc8sh0QQ+FrMFQIAgY2cIWuUx9LoutWsxNs9udaxDKDb+7Wzth+huRRmlcJANrW148NjJDdF2Db2t7EzUUkwpqAx8EaoWRUyCXgVx5L1QUeQQDBGwFhIYDAxNNHJubBQqPBiWmeWqdWG+6EmrBxJZwxbqjyMnHy87P0BMJADs="));
					case "css" :
					c("text/css");
					$p = "position:fixed;top:";
					$s = "text-shadow:2px 2px 2px #FFF;";
					$c = "rgba(136,146,191";
					die("#pe_p{" . $p . "0;z-index:999;left:0;width:100%;padding:0 2px 0 32px;background-color:$c,0.9);background:linear-gradient($c,0.4),$c,0.6),$c,0.8),$c,0.9),$c,1) 90%,rgba(0,0,0,1));height:31px !important;font-family:helvetica;font-size:14px !important;line-height:20px !important;}#pe_p SPAN{margin:0 5px 0 0;cursor:pointer;}#pe_p UL{list-style-type:none;margin:3px;padding:0;}#pe_p IMG{border:0;vertical-align:middle;padding-right:4px;}#pe_p A{text-decoration:none;color:#000;" . $s . "}#pe_p .menu {position:fixed;top:8px;left:90px;}#pe_p .stat SPAN{display:inline-block;" . $s . "}#pe_p LI{cursor:pointer;}#pe_p LI:hover{background:#F0F0F0;}#pe_p .stat{" . $p . "6px;right:48px;}#pe_p .sub{" . $p . "28px;display:inline;background:#FFF;border:solid 1px #808080;box-shadow:2px 2px 6px #000;z-index:1000;}#pe_p .menu_i{padding:5px 6px 5px 6px;" . $s . "}#pe_p .menu_a{padding:4px 5px 5px 5px;border-top:solid #000 1px;border-left:solid #000 1px;border-right:solid #000 1px;background:#FFF;}@media print{#pe_p{display:none;}}");
					default : //! serve real cache requests
					if(! empty(self::$mc)) {
						$c = self::$mc->get("c_$d");
						if(self::$w && a($c) && ! empty($c[ 'd' ])) {
							c((! empty($c[ 'm' ]) ? $c[ 'm' ] : "text/plain"));
							die($c[ 'd' ]);
						}
					}
					die(k($C) . "-E: " . $d);
				}
			}
			$LA = self::$client->lang;
			//! proxy dynamic assets (vendor directory is not accessable by the webserver, only public dir)
			if(ia($this->app, [ "css", "js", "images" ])) {
				function b($a, $b)
				{
					c($a == "css" ? "text/css" : ($a == "js" ? "text/javascript" : "image/png"));
					die(minify($b, $a));
				}
				$N = 'a_' . sha1($this->base . $u . "_" . self::$user->id . "_" . $LA);
				$d = "";
				if(! empty(self::$mc))
					$d = self::$mc->get($N);
				if(! empty($d))
					b($this->app, $d);
				else
				{
					//! patch core.js url to allow per language cache
					foreach([ $this->$a, pr("/^core\.[^\.]+\.js/", "core.js", $this->$a) . PE, $this->$a . ($this->item ? "/" . $this->item : "") ] as $p) {
						$A = N . "*/" . $this->app . "/" . s($p, [ "*" => "", ".." => "" ]);
						$c = @glob($A, GLOB_NOSORT)[ 0 ];
						if($c) {
							if(f($c) && w($c, - 4) != PE) {
								$d = g($c);
								$this->cachettl *= 10;
							}
							else
							{
								ob_start();
								io($c);
								$d = o();
							}
						}
						if(! empty($d)) {
							if(! empty(self::$mc) && empty($this->nocache))
								$this->_ms($N, $d);
							b($this->app, $d);
						}
					}
				}
				$c = "404 Not Found";
				header("HTTP/1.1 $c");
				die;
			}
			//! register core, user and client to templater
			self::$o = [];
			self::$o[ "core" ] = &$this;
			self::$o[ "user" ] = &self::$user;
			self::$o[ "client" ] = &self::$client;
			//! register default meta keywords
			$this->meta[ "viewport" ] = "width=device-width,initial-scale=1.0";
			$A = P . "ctrl/";
			//! get action
			$X = [];
			$w = 0;
			if(! empty($n)) {
				$this->app = $n;
				$this->$a = ! empty($ac) ? $ac : $a;
				$f = "";
			}
			//! url routing
			elseif(! empty($c) && ! empty(self::$r)) {
				//! check routes, best match policy
				uasort(self::$r, function($a, $b)
				{
					return strcmp($b[ 0 ], $a[ 0 ]); 

				});
				foreach(self::$r as $v) {
					if(p("!^" . s($v[ 0 ], "!", "") . "!i", $u, $X)) {
						//! check filter
						if(! self::_cf($v[ 3 ])) {
							$w = 1;
							continue;
						}
						//! chop off whole match (first index) from arguments
						array_shift($X);
						//! get class and action
						$d = $v[ 1 ];
						$f = $v[ 2 ];
						break;
					}
				}
			}
			//! if there was a match but failed due to filters,
			//! set output to 403 Access Denied page
			if(empty($d))
				$d = $w ? "403" : $this->app;
			//! rotate security tokens
			$R = $_REQUEST;
			if(self::$w) {
				$c = $this->app . "." . $this->$a;
				$S = ! empty($_SESSION[ "pe_s" ][ $c ]) ? $_SESSION[ "pe_s" ][ $c ] : "";
				for($i = 1; $i <= 9; $i++ )
					if(isset($R[ "pe_try" . $i ]) && ! empty($R[ "pe_s" ]) && $R[ "pe_s" ] == $S) {
						$this->try = $i;
						$this->form = ! empty($R[ 'pe_f' ]) ? r($R[ 'pe_f' ]) : "";
						$_SESSION[ "pe_s" ][ $c ] = 0;
						break;
					}
				if(empty($_SESSION[ "pe_s" ][ $c ]))
					$_SESSION[ "pe_s" ][ $c ] = sha1(uniqid() . $this->id);
			}
			if(empty($f))
				$f = $a . "_" . t($this->$a);
			//! handle admin login before Users login method gets called
			if($this->app == "login" || $d == "login") {
				$A = "admin";
				if($this->istry() && ! empty($R[ 'id' ]) && $R[ 'id' ] == $A) {
					//don't accept password in GET parameter
					if(! empty($this->masterpasswd) && empty(self::$user->id) && password_verify($_POST[ 'pass' ], $this->masterpasswd)) {
						self::log("A", "Login " . L($A), "users");
						$_SESSION[ "pe_u" ]->id = - 1;
						$_SESSION[ "pe_u" ]->name = L($A);
						//! don't allow Users class to log in admin, that's Core's job
						$this->redirect();
					}
				}
				if($_SESSION[ "pe_u" ]->id)
					$this->redirect("/");
			}
			elseif($this->app == "logout" || $d == "logout") {
				$i = self::$user->id;
				if($i) {
					self::log("A", "Logout " . self::$user->name, "users");
					//! hook Users class log out method
					if($i != - 1 && q(self::$user, "logout"))
						self::$user->logout();
				}
				session_destroy();
				$this->redirect("/");
			}
			//! caching allowed?
			$A = ! empty(self::$mc) && empty($this->nocache);
			//! load application
			if(! cc($d)) {
				//! application class should be already autoloaded. But if not
				if($d[ 0 ] == "\\")
					$d = w($d, 1);
				$C = t($d);
				$D = ucfirst($d);
				$E = $this->$a;
				$F = t($E);
				$G = ucfirst($E);
				$P = "app/ctrl/";
				$H = N . "*/ctrl/";
				foreach(array_unique([ "$P$d" . "_$E" . PE, "$P$C" . "_$F" . PE, "$P$D" . "_$G" . PE, "$P$d" . PE, "$P$C" . PE, "$P$D" . PE, "$H$d" . "_$E" . PE, "$H$C" . "_$F" . PE, "$H$D" . "_$G" . PE, "$H$d" . PE, "$H$C" . PE, "$H$D" . PE ]) as $v) {
					$c = @glob($v);
					if(! empty($c[ 0 ])) {
						io($c[ 0 ]);
						self::$p = n(n($c[ 0 ]));
						break;
					}
				}
			}
			$G = f(P . "sql/pages.sql");
			$P = C . "App";
			$D = "Ctrl\\";
			$V = "pe_v";
			//! add namespace if applicable
			if(cc(C . $d))
				$d = C . $d;
			elseif(cc(C . $D . $d))
				$d = C . $D . $d;
			//! *** APP Event ***
			list($d, $f) = $this->_eh("app", [ $d, $f ]);
			//! do we have a valid application or controller?
			$D = $o = [];
			if(! cc($d)) {
				//! no, fail on CLI
				if(! self::$w)
					die("PHPPE-C: " . L($d . "_$a not found!") . "\n");
				//! fallback to default application on CGI
				$d = $P;
				//! look for cms content from database - only for primary datasource
				if(! empty(Core::db(0)) && $G) {
					try {
						$T = "template";
						//! if found in cache
						$C = 'd_' . sha1($this->base . $u);
						if($A)
							$D = self::$mc->get($C);
						//! look for a page in database
						//! best match policy
						if(empty($D[ $T ])) {
							Core::ds(0);
							$D = Core::fetch("*", "pages", "(id=? OR ? LIKE id||'/%') AND (lang='' OR lang=?) AND pubd<=CURRENT_TIMESTAMP AND (expd=0 OR expd>CURRENT_TIMESTAMP)", "", "id DESC,created DESC", [ $u, $u, $LA ]);
							if($A && ! empty($D[ $T ]))
								$this->_ms($C, $D);
						}
						if(! empty($D[ $T ])) {
							//check filters
							if(! self::_cf($D[ 'filter' ])) {
								//! not allowed, fallback to 403
								$this->$T = "403";
								throw new \Exception();
							}
							//! PHPPE\Content loads further controllers generated by PHPPE CMS
							array_unshift($X, $D);
							$d = C . "Content";
							$f = $a;
							//! set view for page
							Core::$core->template = $D[ $T ];
							//! load application property overrides
							$o = @jd($D[ 'data' ]);
							if(! a($o))
								$o = [];
							//! get extra parameters from url
							$c = w($u, u($D[ 'id' ]) + 1);
							$o[ 'params' ] = x("/", $c);
						}
					}
					catch(\Exception $e) {
						$D = [];
					}
				}
			}
			//! get configuration array for Application
			$p = s($d, [ "PHPPE\\App\\" => "", "PHPPE\\Ctrl\\" => "" ]);
			foreach([ $p, $this->app ] as $C) {
				$c = @include(N . "$C/config" . PE);
				if(a($c))
					break;
			}
			//! get validators from previous view generation
			if(! empty($_SESSION[ $V ]))
				self::$v = $_SESSION[ $V ];
			//! create Application object
			self::$o[ "app" ] = $app = new $d(a($c) ? $c : []);
			if(! q($app, $f))
				$f = $a;
			//! Application constructor may alter template, so we have to log this after "new App"
			self::log("D", $this->app . "/" . $this->$a . " ->$d::$f " . $this->template, "routes");
			//! Application constructor may requested cache to be off
			$A &= empty($this->nocache);
			//! in maintenance mode only a view displayed
			$m = "maintenance";
			if(empty($this->$m) || ! self::$w) {
				//! output view with templater
				$C = 'p_' . sha1($this->base . $u . "_" . self::$user->id . "_" . $LA);
				$T = "";
				//only read from cache if application allows
				if($A && ! self::istry()) {
					$p = self::$mc->get($C);
					if(a($p)) {
						foreach([ 'm' => 'meta', 'c' => 'css', 'j' => 'js', 'J' => 'jslib' ] as $k => $v)
							if(a($p[ $k ]))
								$this->$v = array_merge($this->$v, $p[ $k ]);
						$T = $p[ 'd' ];
					}
				}
				//! if it was not found in cache
				//! application may request empty output by clearing $core->template
				if(! $T) {
					//! get frame
					$p = [];
					$d = "dds";
					if($G) {
						try {
							$F = self::fetch("*", "pages", "id='frame'");
							$E = @jd($F[ 'data' ]);
							self::$o[ 'frame' ] = $E;
							//! load global dds
							$E = @jd($F[ $d ]);
							if(a($E))
								$p += $E;
						}
						catch(\Exception $e) {
						}
					}
					//! if we're serving Contents
					if(! empty($D)) {
						//! page local dynamic data sets
						$e = jd($D[ $d ]);
						if(a($e))
							$p += $e;
						//! load site title
						$this->site = $D[ 'name' ];
						//! load application properties
						foreach([ "id", "name", "template", "lang", "modifyd" ] as $v)
							$o[ $v ] = $D[ $v ];
					}
					//! load dynamic data sets into app properties
					if(a($p)) {
						foreach($p as $k => $c)
							if(! ia($k, [ $d, "id" ])) {
								try {
									$o[ $k ] = @self::query($c[ 0 ], $c[ 1 ], s(@$c[ 2 ], "@ID", $k), @$c[ 3 ], @$c[ 4 ], @$c[ 5 ], self::getval(@$c[ 6 ]));
								}
								catch(\Exception $e) {
									self::log("E", $D[ 'id' ] . " " . $e->getMessage() . " " . implode(" ", $c), $d);
								}
							}
					}
					//! load property overrides
					foreach($o as $k => $v)
						if($k != $d)
							$app->$k = $v;
					//! *** CTRL Event (Controller action) ***
					$this->_eh("ctrl", [ $d, $f ]);
					//! call action method
					if(q($app, $f))
						! empty($X) ? call_user_func_array([ $app, $f ], $X) : $app->$f($this->item);
					if(! empty($app->_meta))
						$this->meta = array_merge($this->meta, $app->_meta);
					//! clear validators
					$_SESSION[ $V ] = [];
					if(! empty($this->template)) {
						$T = $this->template($this->template);
						//if action specific template not found, fallback to application's
						if(! $T)
							$T = $this->template($this->app);
						if(! $T && self::$w)
							$T = $this->template("404");
					}
					if(empty($this->noframe)) {
						//! replace application marker in frame with output
						$d = $this->template("frame");
						//! failsafe frame
						//if( !$d ) $T = "<div id='content'>".$T."</div>"; elseif
						if(p("/<!app>/ims", $d, $m, PREG_OFFSET_CAPTURE))
							$T = z($d, 0, $m[ 0 ][ 1 ]) . $T . w($d, $m[ 0 ][ 1 ] + 6);
					}
					//save to cache
					if($A && $T)
						$this->_ms($C, [ "m" => $this->meta, "c" => $this->css, "j" => $this->js, "J" => $this->jslib, "d" => $T ]);
				}
			}
			else
			{
				$T = $this->template($m);
				if(empty($T))
					$T = L(ucfirst($m));
			}
			//! check dump argument here, by now all core properties are populated
			if((@ia("--dump", $_SERVER[ 'argv' ]) || isset($R[ '--dump' ])) && $this->runlevel > 1) {
				c("text/plain");
				print_r($_SERVER);
				print_r(self::$o);
				die;
			}
			//! close all database connections before output
			if(! empty(self::$db))
				foreach(self::$db as $d)
					if(q($d, "close"))
						$d->close();
			self::$db = [];
			self::$s = 0;
			//! *** VIEW Event ***
			$T = $this->_eh("view", $T);
			//! ***** HTTP response *****
			$o = isset($app->_output) ? $app->_output : $this->output;
			if(self::$w) {
				header("Pragma:no-cache");
				header("Cache-Control:no-cache,no-store,private,must-revalidate,max-age=0");
				header("Content-Type:" . (! empty($app->_mimetype) ? $app->_mimetype : "text/" . (! empty($o) ? $o : "html")) . ";charset=utf-8");
			}
			//! output header
			if(! empty($o)) {
				$c = @glob(N . "*/out/" . $o . "_header" . PE);
				if(! empty($c[ 0 ]))
					io($c[ 0 ]);
				elseif($o == "html") {
					$P = empty($this->nopanel) && self::$user->has("panel");
					$O = "";
					$I = m(__FILE__) . "/";
					if($I == I . ".php/")
						$I = "";
					$B = "http" . ($this->sec ? "s" : "") . "://" . $this->base;
					//! HTML5 header and title
					$O .= "<!DOCTYPE HTML>\n<html lang='" . $LA . "'><head><title>" . (! empty($this->site) ? $this->site : $this->id) . "</title><base href='$B'/><meta charset='utf-8'/>\n<meta name='Generator' content='" . $this->id . "'/>\n";
					foreach($this->meta as $k => $m)
						if($m)
							$O .= "<meta name='$k' content='" . h($m) . "'/>\n";
					//! favicon
					$O .= "<link rel='shortcut icon' href='" . (empty($app->_favicon) ? 'favicon.ico' : $app->_favicon) . "'/>\n";
					//! add style sheets (async)
					//			$d = "<link rel='stylesheet' type='text/css' href='%s' media='screen,print'/>\n";
					$O .= "<style media='all'>\n";
					$d = "@import url('%s');\n";
					$N = $this->base . "_" . $this->app . "." . $this->$a . "_" . self::$user->id . "_" . $LA;
					//! admin css if user logged in and has access
					if($P)
						$O .= sprintf($d, "$I?cache=css");
					//! user stylesheets
					if(! empty($this->css)) {
						//! if aggregation allowed
						if(! empty(self::$mc) && empty($this->noaggr)) {
							$n = sha1($N . "_css");
							if(empty(self::$mc->get("c_$n"))) {
								$da = "";
								//! skip dynamic assets (they use a different caching mechanism)
								foreach($this->css as $u => $v)
									if($v && w($v, - 3) != "php" && $u[ 0 ] != "?")
										$da .= minify(r(g($v)), "css") . "\n";
								//! save result to cache
								$this->_ms("c_$n", [ "m" => "text/css", "d" => $da ]);
							}
							$O .= sprintf($d, "$I?cache=$n");
							//! add dynamic stylesheets, they were left out from aggregated cache above
							foreach($this->css as $u => $v)
								if($v && ($u[ 0 ] == "?" || w($v, - 3) == "php"))
									$O .= sprintf($d, ($u[ 0 ] == "?" ? "" : $I . "css/") . $u);
						}
						else
						{
							foreach($this->css as $u => $v)
								if($v)
									$O .= sprintf($d, ($u[ 0 ] == "?" ? "" : $I . "css/") . $u);
						}
					}
					$O .= "</style>\n";
					//! add javascript libraries (async)
					$d = "<script type='text/javascript'";
					$e = "</script>\n";
					$a = " async src='" . $I . "js/";
					if(! empty($this->jslib)) {
						//! if aggregation allowed
						if(! empty(self::$mc) && empty($this->noaggr)) {
							$n = sha1($N . "_js");
							if(empty(self::$mc->get("c_$n"))) {
								$da = "";
								//! skip dynamic assets (they use a different caching mechanism)
								foreach($this->jslib as $u => $v)
									if($v && w($v, - 3) != "php")
										$da .= minify(r(g($v)), "js") . "\n";
								$this->_ms("c_$n", [ "m" => "text/javascript", "d" => $da ]);
							}
							$O .= "$d async src='$I?cache=$n'>$e";
							//! add dynamic javascripts, they were left out from aggregated cache above
							foreach($this->jslib as $u => $v)
								if($v && w($v, - 3) == "php")
									$O .= "$d$a$u'>$e";
						}
						else
						{
							foreach($this->jslib as $u => $v)
								if($v)
									$O .= "$d$a$u'>$e";
						}
					}
					//load PHPPE\Users library if it's not aggregated already and PHPPE panel is shown
					$c = "users.js";
					if($P && ! isset($this->jslib[ $c ]) && f(N . "users/js/" . $c . PE))
						$O .= "$d$a$c'>$e";
					//! add javascript functions
					$c = $this->js;
					$a = "";
					if($P && empty($this->css[ "core.css" ])) {
						$x = "document.getElementById('pe_'";
						$y = ".style.visibility";
						$a = "pe_t=setTimeout(function(){pe_p('');},2000)";
						$c[ "L(t)" ] = "return t.replace(/_/g,' ');";
						$c[ 'pe_p(i)' ] = "var o=$x+i);if(pe_t!=null)clearTimeout(pe_t);if(pe_c&&pe_c!=i)$x+pe_c)$y='hidden';pe_t=pe_c=null;if(o!=null){if(o$y=='visible')o$y='hidden';else{o$y='visible';pe_c=i;$a;}}return false;";
						$c[ 'pe_w()' ] = "if(pe_t!=null)clearTimeout(pe_t);$a;return false;";
						$a = ",pe_t,pe_c";
					}
					if(! empty($c)) {
						$O .= $d . ">\nvar pe_ot=" . ($P ? 31 : 0) . "$a;\n";
						foreach($c as $fu => $co)
							$O .= "function $fu {" . $co . "}\n";
						$O .= $e;
					}
					$O .= "</head>\n<body" . (! empty($this->js[ "init()" ]) ? " onload='init();'" : "") . ">\n";
					//! display PHPPE panel
					if($P) {
						$H = " class='sub' style='visibility:hidden;' onmousemove='return pe_w();'";
						$O .= "<div id='pe_p'><a href='" . url("/") . "'><img src='$I?cache=logo' alt='" . $this->id . "' style='margin:3px 10px -3px 10px;'></a><div class='menu'>";
						//! menu items and submenus
						$x = 0;
						if(! empty($this->menu)) {
							foreach($this->menu as $e => $L) {
								//! access check
								@list($ti, $a) = x("@", $e);
								if(! empty($a) && ! self::$user->has($a))
									continue;
								$a = 0;
								if(a($L))
									$l = $L[ array_keys($L)[ 0 ] ];
								else 
									$l = $L;
								$U = $_SERVER[ 'REQUEST_URI' ];
								if(z($l, 0, u($U)) == $U || "/" . z($l, 0, u($U)) == $U)
									$a = 1;
								else
								{
									$d = x("/", $l);
									if(empty($d[ 0 ]))
										$d = array_shift($d);
									if($this->app == (! empty($d[ 0 ]) ? $d[ 0 ] : I))
										$a = 1;
									unset($d);
								}
								if(a($L)) {
									$O .= "<div id='pe_m$x'$H><ul>";
									//						$X=0;
									foreach($L as $t => $l)
										if($t) {
											@list($Y, $A) = x("@", $t);
											if(empty($A) || self::$user->has($A))
												/*								if(a($l)) {
									$O.="<li onclick='return pe_p(\"m$x_$X\");'>".h(L($Y))."<div id='pe_m$x_$X'$H><ul>";
									foreach( $L  as $k => $v ){
										@list($Y,$A)=x("@",$k);
										if(empty($A) || self::$user->has($A))
										$O.= "<li onclick=\"document.location.href='".$I."$k';\"><a href='".($I?$I."/":"")."$k'>" . h( L($Y) ) . "</a></li>";
									}
									$O.="</div></li>";
								} else */
												$O .= "<li onclick=\"document.location.href='" . $I . "$l';\"><a href='" . $I . "$l'>" . h(L($Y)) . "</a></li>";
												//							$X++;
											}
									$O .= "</ul></div><span class='menu_" . ($a ? "a" : "i") . "' onclick='return pe_p(\"m$x\");'>" . h(L($ti)) . "</span>";
									$x++ ;
								}
								else 
									$O .= "<span class='menu_" . ($a ? "a" : "i") . "'><a href='" . $I . "$L'>" . h(L($ti)) . "</a></span>";
							}
						}
						//! call modules status hooks
						$O .= "</div><div class='stat'>";
						//! *** STAT Event ***
						foreach($this->libs as $d)
							if(q($d, "stat"))
								$O .= "<span>" . $d->stat() . "</span>";
						//! language selector box
						$O .= "<div id='pe_l'$H><ul>";
						if(! empty($_SESSION[ 'pe_ls' ]))
							$d = $_SESSION[ 'pe_ls' ];
						else
						{
							//if application has translations, use that list
							//if not, fallback to core's translations
							$D = array_unique(@scandir("app/lang") + @scandir(P . "lang"));
							$d = [];
							foreach($D as $f)
								if(w($f, - 4) == ".php")
									$d[ z($f, 0, u($f) - 4) ] = 1;
							$_SESSION[ 'pe_ls' ] = $d;
						}
						foreach($d as $k => $v)
							if($k)
								$O .= "<li><a href='" . url() . "?lang=$k'><img src='images/lang_$k.png' alt='$k' title='$k'>" . ($k != L($k) ? "&nbsp;" . L($k) : "") . "</a></li>";
						$O .= "</ul></div>";
						//! current language and user menu
						$k = $LA;
						$f = "images/lang_$k.png";
						$c = ! empty($_SESSION[ 'pe_c' ]);
						$O .= "<span onclick='return pe_p(\"l\");'>" . (f(P . $f) ? "<img src='$f' height='10' alt='$k' title='$k'>" : $k) . "</span><div id='pe_u'$H><ul><li onclick='pe_p(\"\");if(typeof users_profile==\"function\")users_profile(this);else alert(\"" . L("Install PHPPE Pack") . "\");'>" . L("Profile") . "</li>" . (self::$user->has("conf") ? "<li><a href='" . url() . "?conf=" . (1 - $c) . "'>" . L(($c ? "Lock" : "Unlock")) . "</a></li>" : "") . "<li><a href='" . url("logout") . "'>" . L("Logout") . "</a></li></ul></div><span onclick='return pe_p(\"u\");'>" . (! empty(self::$user->name) ? self::$user->name : "#" . self::$user->id) . "</span></div></div><div style='height:32px !important;'></div>\n";
					}
					echo $O;
				}
			}
			//! output application
			echo($T);
			//! do footer stuff, monitoring stats
			if(! empty($o)) {
				$c = @glob(N . "*/out/" . $o . "_footer" . PE);
				if(! empty($c[ 0 ]))
					io($c[ 0 ]);
				elseif($o == "html") {
					$D = count($this->error);
					$d = 'REQUEST_TIME_FLOAT';
					$T = ! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : $this->started;
					if($D > 0 && empty($this->noerror)) {
						$c = "";
						foreach($this->error as $v)
							$c .= implode("\n", $v) . "\n";
						echo("<script type='text/javascript'>\nalert('" . s(ad(r($c)), "\n", "\\n") . "');\n</script>\n");
					}
					echo("\n<!-- MONITORING: " . ($D > 0 ? "ERROR" : ($this->runlevel > 0 ? "WARNING" : "OK")) . ", page " . sprintf("%.4f sec, db %.4f sec, server %.4f sec, mem %.4f mb%s -->\n</body>\n</html>\n", microtime(1) - $T, self::$b, $this->started - $T, memory_get_peak_usage() / 1024 / 1024, ! empty(self::$mc) ? ", mc" : ""));
				}
			}
			//! make sure to flush session
			session_write_close();
		}

/*** Core library ***/
/**
 * load a new language dictionary into memory
 *
 * @param class name (module name)
 */
		static function langInit($c = "", $i = [])
		{
			if($c == "")
				$c = m(n(n(d(2))));
			//! failsafe
			$L = empty(self::$client->lang) ? "en" : self::$client->lang;
			//! expand language dictionary
			if(empty($i[ 0 ]))
				$i = x("_", s($L, "-", "_"));
			//! get translations
			$la = "";
			$c = N . "$c/lang/";
			//! first check as is, then first part, finally English
			//! eg.: hu_HU, hu, en; en_US, en
			foreach([ $L, $i[ 0 ], "en" ] as $l) {
				if(f($c . $l . PE)) {
					$la = io($c . $l . PE);
					break;
				}
			}
			//! merge into core's array returned by l()
			if(a($la))
				self::$l += $la;
		}
/**
 * log a message
 *
 * @param weight, Debug | Info | Audit | Warning | Error | Critical
 * @param message in English (don't translate it for compatibility)
 * @param module name, guessed if not given
 */
		static function log($w, $m, $n = null)
		{
			if(! is_string($m) || self::$core->runlevel < 3 && $w == "D")
				return;
			//! log a message of weight for a module
			$w = k($w);
			if(! ia($w, [ "D", "I", "A", "W", "E", "C" ]))
				$w = "A";
			if(empty($n))
				$n = ! empty(self::$core->app) ? self::$core->app : "core";
			//! remove sensitive information from message
			$m = r(s($m, n(n(__FILE__)) . "/", ""));
			$g = ! empty(self::$l[ $m ]) ? L($m) : $m;
			//! debug trace
			$t = "";
			if(! empty(self::$core->trace)) {
				$s = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				foreach($s as $d)
					$t .= "\t" . w($d[ 'file' ], u(n(__DIR__)) + 1) . ":" . @$d[ 'line' ] . ":" . $d[ 'function' ] . "\n";
			}
			$e = e(0);
			//! log always stores dates in UTC to be comparable among servers
			date_default_timezone_set("UTC");
			$p = date("Y-m-d") . "T" . date("H:i:s") . "Z-$w-" . k($n) . ": ";
			//! send message to syslog too
			if($w == "A" || $w == "C" || self::$core->syslog)
				syslog($w == "C" ? LOG_ERR : LOG_NOTICE, self::$core->base . ":PHPPE-$w-" . k($n) . ": " . s($m, [ "\n" => "\\n", "\r" => "\\r" ]) . s($t, "\n", ""));
			//! save message to file
			$l = P . "log/" . $n . ".log";
			if(! self::$core->syslog && ! file_put_contents($l, $p . s($m, [ "\n" => "\\n", "\r" => "\\r" ]) . "\n" . $t, FILE_APPEND | LOCK_EX)) {
				$w = "C";
				$g .= (! self::$w ? "\nLOG-C" : "<br/>\n" . date("Y-m-d") . "T" . date("H:i:s") . "Z-C-LOG") . ": " . L("unable to write") . " $l";
			}
			@chmod($l, 0660);
			//! on critical message, bail out
			if($w == "C") {
				if(self::$core->output != "html")
					die(k("$n-C") . ": " . $g . "\n" . $t);
				die("\n<html><body style='margin:8px;background:#000000;color:#A00000;'><div style='text-align:center;font-size:28px;color:#ff0000;'>PHPPE" . VERSION . " - " . L("Developer Console") . "</div><br/><br/>\n$p" . nl2br(s("$g\n$t", "\t", "&nbsp;&nbsp;")) . "</body></html>\n");
			}
			elseif(! self::$w && $w != "D" && $w != "I")
				fwrite(STDERR, k("$n-$w") . ": " . $g . "\n");
			//! restore timezone and error reporting
			date_default_timezone_set($_SESSION[ 'pe_tz' ]);
			e($e);
			return true;
		}
/**
 * query a library instance or list all registered libraries (modules)
 * @usage lib()
 * @param name, optional
 * @param array of library instances or one specific instance
 *
 * query a library instance
 * @usage lib(n)
 * @param name, optional
 * @param library instance or null
 *
 * register a library (module) in PHPPE
 * @usage lib(n,...) call it from your library's constructor
 * @param name
 * @param description
 * @param dependency, array of comma separated list
 * @param instance reference if applicable
 */
		static function lib($n = "", $l = "", $D = "", &$o = null)
		{
			$L = &self::$core->libs;
			//! return list of lib or a specific module instance
			if($l == "" && empty($D) && ! $o)
				return empty($n) ? $L : (empty($L[ $n ]) ? null : $L[ $n ]);
			//! initialize a module with dependency check
			$d = "";
			//! if name not given, guess from filename
			if(empty($n)) {
				$n = m(d(1));
				if(p("/([0-9]+\_)?([^\/\_]+)([^\/]*)\.php$/i", $n, $m))
					$n = $m[ 2 ];
				unset($m);
			}
			//! check dependencies
			if($D) {
				if(! a($D))
					$D = x(",", $D);
				foreach($D as $v)
					if(! self::isInst($v))
						$d .= ($d ? "," : "") . $v;
				if($d)
					self::log("C", "$n depends on: $d");
			}
			//! if there's a name and no failed dependency, add to list
			if(! $d && $n) {
				if(empty($L[ $n ]))
					$L[ $n ] = $o ? $o : new \StdClass();
				$L[ $n ]->name = L(empty($l) ? $n : $l) . (! empty($o->version) ? " (" . $o->version . ")" : "");
			}
		}
/**
 * return all installed (not just registered) add-ons
 * @usage addon()
 * @return array of add-ons
 *
 * register an add-on in PHPPE
 * @usage addon(n,...) call it from your add-on's init() method
 * @param name
 * @param description
 * @param dependencies
 * @param configuration string (see self::addon() calls in bootstrap)
 */
		static function addon($n = "", $l = "", $D = "", $c = "")
		{
			if(empty($n)) {
				//! save current context
				$S = [ self::$core->addons, self::$core->js, self::$core->jslib, self::$core->css ];
				//! return all available add-ons
				$d = @glob(N . "*/addons/*" . PE);
				foreach($d as $f) {
					$F = m($f);
					$d = io($f);
					$w = z($F, 0, u($F) - 4);
					$W = A . $w;
					if(ce($W)) {
						//! make a fail safe call
						self::addon($w, "addon $w");
						//! let the plugins call self::addon() as they like
						if(q($W, "init")) {
							$W = new $W([], $w, $w);
							$W->init();
							unset($W);
						}
					}
				}
				//! save result
				$r = self::$core->addons;
				//! restore context
				list(self::$core->addons, self::$core->js, self::$core->jslib, self::$core->css) = $S;
				return $r;
			}
			//! register an add-on with dependency check
			$d = "";
			//! check dependencies
			if($D) {
				if(! a($D))
					$D = x(",", $D);
				foreach($D as $v)
					if(! self::isInst($v))
						$d .= ($d ? "," : "") . $v;
			}
			if(empty($d)) {
				self::$core->addons[ $n ] = (object)[ 'name' => L(empty($l) ? $n : $l), 'conf' => $c ];
				LANG_INIT($n);
			}
			else 
				self::log("E", "$n depends on: $d");
		}
/**
 * checks if a module or an add-on is installed or not
 *
 * @param name
 * @return boolean true or false
 */
		static function isInst($n)
		{
			//! check for installed module or available add-on
			return(isset(self::$core->libs[ $n ]) || isset(self::$core->addons[ $n ]) || ce(C . $n) || ce(A . $n) || ! empty(@glob(N . "*/addons/" . $n . PE)[ 0 ]));
		}
/**
 * format an error message
 *
 * @param weight (see log())
 * @param module
 * @param message
 * @return formated message
 */
		static function e($w, $c, $m)
		{
			if(! is_string($m))
				$m = json_encode($m);
			return ! empty(self::$core->output) && self::$core->output == "html" ? "<span style='background:#F00000;color:#FEA0A0;padding:3px;'>" . ($w ? "$w-" : "") . ($c ? "$c:&nbsp;" : "") . h($m) . "</span>" : "$c-$w: " . s($m, [ "\r" => "", "\n" => "\\n" ]) . "\n";
		}
/**
 * add an error message to output
 *
 * @param message
 * @param if message is related to a field, it's name
 */
		static function error($m = "", $f = "")
		{
			if(empty($m))
				return self::$core->error;
			//! register an error message
			if(! isset(self::$core->error[ $f ]))
				self::$core->error[ $f ] = [];
			self::$core->error[ $f ][ r($m) ] = r($m);
			//log validation error in developer and debug mode
			if(self::$core->runlevel > 1)
				self::log("E", $f . "@" . $_SERVER[ 'REQUEST_URI' ] . " " . $m, "validate");
		}
/**
 * check for errors
 *
 * @param if interested in errors for a specific field, it's name
 * @return boolean true or false
 */
		static function isError($f = "")
		{
			//! check for error
			return ! empty($f) ? isset(self::$core->error[ $f ]) : ! empty(self::$core->error);
		}
/**
 * query routing table
 * @usage route()
 * @return array of routing rules
 *
 * register a new url route. This method can handle many different input formats
 * @usage route(...) call it from your initialization code, app/99_app.php
 * @param regexp mask of url
 * @param class in which the app resides
 * @param method of the application action handler (if not given, default action routing applies)
 * @param filters comma separated list or array of filters (ACE has to be started with '@' or PHPPE\Filter\*::filter() will be used)
 */
		static function route($u = "", $n = "", $a = "", $f = [])
		{
			if(empty($u))
				return self::$r;
			$A = "action";
			$F = "filters";
			$U = "url";
			$N = "name";
			//! standard arguments
			if(is_string($u) && ! empty($n)) {
				if(! a($f))
					$f = x(",", $f);
				self::$r[ j($u, $n, $a) ] = [ $u, $n, $a, $f ];
			}
			//! associative array
			elseif(a($u) && ! empty($u[ $U ]) && ! empty($u[ $N ])) {
				$f = ! empty($u[ $F ]) ? $u[ $F ] : [];
				$a = ! empty($u[ $A ]) ? $u[ $A ] : "";
				self::$r[ j($u[ $U ], $u[ $N ], $a) ] = [ $u[ $U ], $u[ $N ], $a, a($f) ? $f : x(",", $f) ];
			}
			//! mass import from an array
			elseif(a($u) && ! empty(current($u)[ 0 ])) {
				foreach($u as $v)
					self::$r[ j($v[ 0 ], $v[ 1 ], (! empty($v[ 2 ]) ? $v[ 2 ] : "")) ] = $v;
			}
			//! from stdClass
			elseif(is_object($u) && ! empty($u->$U) && ! empty($u->$N)) {
				$f = ! empty($u->$F) ? $u->$F : [];
				$a = ! empty($u->$A) ? $u->$A : "";
				self::$r[ j($u->$U, $u->$N, $a) ] = [ $u->$U, $u->$N, $a, a($f) ? $f : x(",", $f) ];
			}
			else 
				self::log("W", "bad route: " . serialize($u));
			//! limit check
			if(count(self::$r) >= 512)
				self::log("C", "too many routes");
		}
/**
 * do security checks if user tries to save a form
 *
 * @return boolean true or false
 */
		static function isTry($f = "")
		{
			//! return button number if user tries to save a form
			return empty($f) || $f == self::$core->form ? self::$core->try : 0;
		}
/**
 * return memcache instance to be later used by applications
 *
 * @usage call it from your application
 * @return memcache instance
 */
		static function mc()
		{
			return self::$mc;
		}

/*** HTTP helpers ***/
/**
 * generate a permanent link (see also url())
 *
 * @param application
 * @param action
 */
		static function url($m = "", $p = "")
		{
			//! generate canonized permanent link
			$c = self::$core->base;
			$f = m(__FILE__);
			if(empty($m) && ! empty(self::$core->app))
				$m = self::$core->app;
			if(empty($p) && ! empty(self::$core->app) && self::$core->app == $m)
				$p = self::$core->action;
			$a = ($m != "/" ? ($m . $p != I . "action" ? $m . "/" : "") . (! empty($p) && $p != "action" ? $p . "/" : "") : "");
			return "http" . (self::$core->sec ? "s" : "") . "://" . $c . ($c[ u($c) - 1 ] != "/" ? "/" : "") . ($f != I . PE ? $f . ($a ? "/" : "") : "") . $a;
		}
/**
 * redirect user
 *
 * @param url to redirect to
 * @param boolean save current url before redirect so that it will be used next time
 */
		static function redirect($u = "", $s = 0)
		{
			//save current url
			if($s)
				self::_r();
			//get redirection url if exists
			if(empty($u) && ! empty($_SESSION[ "pe_r" ])) {
				$u = $_SESSION[ "pe_r" ];
				unset($_SESSION[ "pe_r" ]);
			}
			session_write_close();
			//redirect user
			header("HTTP/1.1 302 Found");
			$f = m(__FILE__);
			header("Location:" . (! empty($u) ? (strpos($u, "://") ? $u : "http" . (self::$core->sec ? "s" : "") . "://" . self::$core->base . ($f != I . PE ? $f . "/" : "") . ($u != "/" ? $u : "")) : self::url() . self::$core->item));
			exit();
		}
/**
 * make a http request and return content. This will follow cookie changes during redirects as well
 *
 * @param url
 * @param post array
 * @param timeout in sec
 * @return content
 */
		static function get($u, $p = "", $T = 3, $l = 0)
		{
			static $C;			
			//! check recursion maximum level
			if($l > 7)
				return;
			//! parse url
			if(p("/^([^\:]+)\:\/\/([^\/\:]+)\:?([0-9]*)(.*)$/", $u, $m)) {
				//! validation and default values
				$s = 0;
				if($m[ 1 ] != "http" && $m[ 1 ] != "https")
					return;
				if($m[ 1 ] == "https") {
					$s = 1;
					$m[ 2 ] = "ssl://" . $m[ 2 ];
				}
				if($m[ 3 ] == "")
					$m[ 3 ] = ($m[ 1 ] == "http" ? 80 : 443);
				if($m[ 4 ] == "")
					$m[ 4 ] = "/";
				//! open socket
				$f = fsockopen($m[ 2 ], $m[ 3 ], $n, $e, $T);
				if(! $f) {
					//log failure
					self::log("E", "$u #$n $e", "http");
					//give it a fallback in case ssl transport not configured in php
					return($s && strpos($e, '"ssl"') ? file_get_contents($u) : "");
				}
				//! construct POST
				$P = a($p) ? http_build_query($p, "_") : "";
				//! send request
				//! we are using HTTP/1.0 on purpose so that we don't have to mess with chunked response
				$o = ($P ? "POST" : "GET") . " " . $m[ 4 ] . " HTTP/1.0\r\nHost: " . $m[ 2 ] . "\r\nAccept-Language: " . self::$client->lang . ";q=0.8\r\n" . ($C ? "Cookie: " . http_build_query($C, "", ";") . "\r\n" : "") . ($P ? "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . u($P) . "\r\n" : "") . "Connection: close;\r\n\r\n" . $P;
				fwrite($f, $o);
				//! receive response
				$d = $H = $n = "";
				$h = "-";
				$t = 0;
				stream_set_timeout($f, $T);
				while(! feof($f) && r($h) != "") {
					//! parse headers
					$h = r((fgets($f, 4096)));
					if(! empty($h))
						$H = t($h);
					if(z($H, 0, 8) == "location")
						$n = r(w($h, 9));
					if(z($H, 0, 12) == "content-type" && strpos($h, "text/"))
						$t = 1;
					//! follow cookie changes
					if(z($H, 0, 10) == "set-cookie") {
						$c = x("=", x(";", r(w($h, 11)))[ 0 ]);
						//c[1] is undefined on nginx when clearing the cookie
						@$C[ $c[ 0 ] ] = $c[ 1 ];
					}
				}
				//! handle redirections
				if($n && $n != $u)
					return self::get($n, $p, $T, $l + 1);
				//! receive data if there was a header (not timed out)
				if($H) {
					while(! feof($f))
						$d .= fread($f, 65535);
					self::log("D", "$u " . strlen($d), "http");
				}
				else 
					self::log("E", "$u timed out $T", "http");
				fclose($f);
				return $t ? s($d, "\r", "") : $d;
			}
		}

/*** Data layer ***/
/**
 * convert human readble value to bytes
 *
 * @param number with unit
 * @return in bytes
 */
		static function si($i)
		{
			//!
			$v = r(ini_get($i));
			$l = t($v[ u($v) - 1 ]);
			switch($l) {
				case 't' :
				$v *= 1024;
				case 'g' :
				$v *= 1024;
				case 'm' :
				$v *= 1024;
				case 'k' :
				$v *= 1024;
			}
			return $v;
		}
/**
 * add a validator on a field value
 *
 * @usage call it *BEFORE* req2obj or req2arr
 * @param field name
 * @param validator name (will use \PHPPE\(validator)::validate)
 * @param is value required
 * @param arguments
 * @param attributes
 */
		static function validate($f, $v, $r = 0, $a = [], $t = [])
		{
			if(q(A . $v, "validate"))
				self::$v[ $f ][ $v ] = [ ! empty($r), $a, $t ];
		}
/**
 * user request to array. Validates user input and returns an array
 *
 * @param form prefix (request name)
 * @param validator data (if given, ovverrides templater's validator list)
 * @return form fields in array
 */
		static function req2arr($p, $V = [])
		{
			//! same as request 2 object
			return self::req2obj($p, $V, 1);
		}
/**
 * user request to object. Validates user input and returns an stdClass
 *
 * @param form prefix (request name)
 * @param validator data (if given, overrides templater's validator list)
 * @return form fields in stdClass
 */
		static function req2obj($p, $V = [], $a = 0)
		{
			//! output format
			if($a)
				$o = array();
			else 
				$o = new \stdClass();
			//! php dropping a warning here is an indicator of hack
			if(! empty(self::$v))
				$V += self::$v;
			$R = $_REQUEST;
			$E = "error";
			//! patch missing elements
			foreach($V as $K => $v) {
				if(z($K, 0, u($p) + 1) == $p . ".") {
					$d = w($K, u($p) + 1);
					$r = $p . "_" . $d;
					foreach($v as $T => $C) {
						//! browsers do not send false for checkboxes
						if(($T == "check" || ! empty($C[ 0 ])) && empty($R[ $r ]))
							$R[ $r ] = $T == "check" ? false : "";
						//! convert localtime in multiple fields to singe timestamp with date validation
						elseif(($T == "date" || $T == "time") && ! empty($R[ $r . ":y" ]))
							list($R[ $r ]) = AddOn\date::validate($K, $r, $C[ 1 ], $C[ 2 ]);
						//! php stores info for files in a separate array, copy it to request
						elseif($T == "file") {
							$f = empty($_FILES[ $r ]) ? [] : $_FILES[ $r ];
							if(! empty($f[ $E ]) && $f[ $E ] != 4)
								self::error(L(ucfirst($d)) . " " . L("failed to upload file."), $K);
							$R[ $r ] = isset($f[ $E ]) && $f[ $E ] == 0 ? $f : [];
						}
					}
				}
			}
			ksort($R);
			//! iterate through form elements with validation
			foreach($R as $k => $v) {
				if(z($k, 0, u($p) + 1) == $p . "_" && $k[ u($k) - 2 ] != ":") {
					$d = w($k, u($p) + 1);
					$K = $p . "." . $d;
					if(isset($V[ $K ])) {
						//iterate on validators for this key
						foreach($V[ $K ] as $T => $C) {
							$t = A . $T;
							if($T == "num")
								$v += 0 . 0;
							if($T == "check")
								$v = $v ? ($v == 1 || $v == '1' ? true : $v) : false;
							if(! empty($C[ 0 ]) && empty($v)) {
								$v = null;
								self::error(L(ucfirst($d)) . " " . L("is a required field."), $K);
							}
							elseif(! empty($v) && q($t, "validate")) {
								list($r, $m) = $t::validate($K, $v, $C[ 1 ], $C[ 2 ]);
								if(! $r && $m) {
									list($O, $f) = x(".", $K);
									//field name and translated error message for user
									self::error(L(ucfirst(! empty($f) ? $f : $O)) . " " . L($m), $K);
								}
							}
						}
					}
					$v = $v == "true" ? true : ($v == "false" ? false : $v);
					if($a)
						$o[ $d ] = $v;
					else 
						$o->$d = $v;
				}
			}
			return $o;
		}
/**
 * convert a user input object (associative array or stdClass) to string attributes
 *
 * @param object
 * @param skip list (array or comma spearated values in string)
 * @param separator (defaults to space)
 * @return string for xml attributes or sql queries
 */
		static function arr2str($o, $s = "", $c = " ")
		{
			return self::obj2str($o, $s, $c);
		}
		static function obj2str($o, $s = "", $c = " ")
		{
			//! get skip list
			if(! a($s))
				$s = x(",", $s);
			//! iterate on fields
			$r = "";
			$d = Core::db();
			if(is_string($o))
				$o = [ $o ];
			foreach($o as $k => $v) {
				if(! ia($k, $s))
					$r .= ($r ? $c : "") . $k . "=" . ($c == "," && ! empty($d) ? $d->quote($v) : "'" . str_replace([ "\r", "\n", "\t", "\x1a" ], [ "\\r", "\\n", "\\t", "\\x1a" ], ad($v)) . "'");
			}
			return $r;
		}
/**
 * convert a value to array by splitting at separator
 *
 * @param input string
 * @param separator (defaults to comma)
 * @return array
 */
		static function val2arr($s, $c = ",")
		{
			//! if input is already an array
			if(a($s))
				return $s;
			elseif($s != "") {
				//! get value of variable
				$v = self::getval($s);
				//! if returned value is already an array
				if(a($v))
					return $v;
				//! if not, explode string
				return x($c, $v);
			}
			return null;
		}
/**
 * flat a recursive array (sub-levels in "_") into simple one level array
 * this is useful if you want to use an option list on trees.
 *
 * @param input tree array
 * @param prefix to use in names
 * @param suffix to use in names (if given, prefix and suffix only appended on nesting)
 * @return flat array
 */
		static function tre2arr($t, $p = "  ", $s = "", $P = "", $S = "", &$d = null)
		{
			//! iterate through array
			foreach($t as $v) {
				//! get output size
				$i = count($d);
				//! look for sub arrays
				foreach($v as $k => $w) {
					if($k != "_") {
						$c = t($k) == "name" && ! $s ? $P . $w . $S : $w;
						if(a($v))
							$d[ $i ][ $k ] = $c;
						elseif(is_object($v))
							$d[ $i ]->$k = $c;
					}
				}
				//! get child items
				if(a($v) && ! empty($v[ "_" ]) || is_object($v) && ! empty($v->_)) {
					//! prefix
					if($s) {
						$c = "\n" . sprintf($p, $i);
						if(a($d[ $i ]) && isset($d[ $i ][ "name" ]))
							$d[ $i ][ "name" ] .= $c;
						elseif(is_object($d[ $i ]) && isset($d[ $i ]->name))
							$d[ $i ]->name .= $c;
					}
					//! recursive call to walk through children elements too
					$d = self::tre2arr(a($v) ? $v[ "_" ] : $v->_, $p, $s, $P . ($s ? "" : $p), ($s ? "" : $s) . $S, $d);
					//! suffix
					if($s) {
						$c = "\n" . $s;
						if(a($d[ count($d) - 1 ]) && isset($d[ count($d) - 1 ][ "name" ]))
							$d[ count($d) - 1 ][ "name" ] .= $c;
						elseif(is_object($d[ count($d) - 1 ]) && isset($d[ count($d) - 1 ]->name))
							$d[ count($d) - 1 ]->name .= $c;
					}
				}
			}
			return $d;
		}

/*** Database layer ***/
/**
 * Initialize a database and make connection available as a data source
 * or without arguments, return database instance for current data source
 *
 * @param empty for query or pdo dsn of new connection
 * @param optional: any PDO compatible class instance
 * @return pdo instance for query or selector for this new data source
 */
		static function db($u = null, $O = null)
		{
			//query
			if(empty($u))
				return self::$db[ self::$s ];
			//initialize a database and make connection available as a data source
			$S = microtime(1);
			$I = "init";
			//create instance
			try {
				//get username and password if it's not part of dsn
				if(! p("/^(.*)@([^@:]+)?:?([^:]*)$/", $u, $d))
					$d[ 1 ] = $u;
				if(! a(self::$db))
					self::$db = [];
				self::$s = count(self::$db);
				self::$db[] = is_object($O) ? $O : new \PDO($d[ 1 ], ! empty($d[ 2 ]) ? $d[ 2 ] : "", ! empty($d[ 3 ]) ? $d[ 3 ] : "", [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => 0 ]);
				if(! isset(self::$db[ self::$s ]))
					throw new \Exception();
				//housekeeping
				$d = &self::$db[ self::$s ];
				$d->id = count(self::$db);
				$d->name = is_object($O) ? get_class($O) : $d->getAttribute(\PDO::ATTR_DRIVER_NAME);
				//! to maintain interoperability among different sql implementations, load replacements from
				//!   vendor/phppe/*/libs/db_(driver).php
				$d->s = @include(@glob(N . "*/libs/ds_" . $d->name . PE)[ 0 ]);
				//register database module
				if(! isset(self::$core->libs[ "ds" ])) {
					self::lib("ds", "DataSource");
					self::$core->libs[ "ds" ]->primary = $d->name;
				}
				if(! empty($d->s[ $I ])) {
					//! driver specific commands for connection
					$c = x(";", $d->s[ $I ]);
					foreach($c as $n => $C)
						if(! empty(r($C)))
							$d->exec(r($C));
				}
			}
			catch(\Exception $e) {
				//! consider failure of first data source fatal
				self::log(self::$s ? "E" : "C", "Unable to $I: $u, " . $e->getMessage(), "db");
				throw $e;
			}
			self::$b += microtime(1) - $S;
			//return selector of newly created instance
			return self::$s;
		}
/**
 * set current data source to use with exec, fetch etc. if argument given
 *
 * @param data source selector
 * @return returns current selector
 */
		static function ds($s = - 1)
		{
			//select a data source to use
			if($s >= 0 && $s < count(self::$db) && ! empty(self::$db[ $s ]))
				self::$s = $s;
			return self::$s;
		}
/**
 * convert a string from user into a sql like phrase
 *
 * @param string
 * @return like string
 */
		static function like($s)
		{
			//user friendly like string conversion
			return pr("/[%_]+/", "%", "%" . pr("/[^a-z0-9\%]/i", "_", pr("/[\ \t]+/", "%", s(r($s), "%", ""))) . "%");
		}
/**
 * common code for executing a query on current data source. All the other methods are wrappers only
 *
 * @param query string
 * @return number of affected rows or data array
 */
		static function exec($q, $a = [])
		{
			//! log query in developer mode
			self::log("D", $q . " " . json_encode($a), "db");
			//! check for valid datasource
			if(! a($a))
				$a = [ $a ];
			if(empty($a[ 0 ]))
				$a = [];
			if(empty(self::$db[ self::$s ]))
				throw new \Exception(L("Invalid ds") . " #" . self::$s);
			//! skip comment lines and empty queries by
			//! reporting 1 affected row to avoid errors on caller side
			$q = r($q);
			if(empty($q) || $q[ 0 ] == '-' || $q[ 0 ] == '/')
				return 1;
			//! do the thing
			$t = microtime(1);
			$r = null;
			$h = self::$db[ self::$s ];
			try {
				//! to maintain interoperability among different sql implementations, a replace
				//! array is used with regexp pattern keys and replacement strings as value
				//! see db() it's initialized there. The array is specified here:
				//!   vendor/phppe/*/libs/db_(driver).php
				if(a($h->s))
					foreach($h->s as $k => $v)
						$q = pr($k, $v, $q);
				$i = t(z($q, 0, 6)) == "select";
				//! prepare and execute the statement with arguments
				$s = $h->prepare($q);
				$s->execute($a);
				//! get result, either an array or a number
				$r = $i ? $s->fetchAll(\PDO::FETCH_ASSOC) : $s->rowCount();
			}
			catch(\Exception $e) {
				//! try to load scheme for missing table
				$E = $e->getMessage();
				if((/*Sqlite/MySQL/MariaDB*/ p("/able:?\ [\'\"]?([a-z0-9_\.]+)/mi", s($E, "le or v", ""), $d) || /*Postgre*/ p("/([a-z0-9_\.]+)[\'\"] does\ ?n/mi", $E, $d) || /*MSSql*/ p("/name:?\ [\'\"]?([a-z0-9_\.]+)/mi", $E, $d)) && ! empty($d[ 1 ])) {
					$c = "";
					$m = "." . t($h->name);
					$d = x(".", $d[ 1 ]);
					$d = r(! empty($d[ 1 ]) ? $d[ 1 ] : $d[ 0 ]);
					list($D) = x("_", $d);
					foreach([ "app/sql/$d", self::$p ? self::$p . "/sql/$d" : "", N . "$D/sql/$d", N . ucfirst($D) . "/sql/$d", P . "sql/$d", N . self::$core->app . "/sql/$d" ] as $f) {
						if($f && f("$f$m.sql")) {
							$c = g("$f$m.sql");
							break;
						}
						if($f && f("$f.sql")) {
							$c = g("$f.sql");
							break;
						}
					}
					if(empty($c)) {
						self::log("E", $E, "db");
						throw $e;
					}
					//! execute schema creation commands
					$c = x(";", $c);
					foreach($c as $n => $C) {
						try {
							if(! empty(r($C)))
								$h->exec(r($C));
						}
						catch(\Exception $e) {
							self::log("C", "creating $d line:" . ($n + 1) . " " . $e->getMessage(), "db");
						}
					}
					self::log("A", "$d created.", "db");
					//! repeat original command
					$s = $h->prepare($q);
					$s->execute($a);
					$r = $i ? $s->fetchAll(\PDO::FETCH_ASSOC) : $s->rowCount();
				}
				else
				{
					self::log("E", $q . " " . json_encode($a) . " " . $E, "db");
					$r = null;
					throw $e;
				}
			}
			//housekeeping
			self::$b += microtime(1) - $t;
			return $r;
		}
/**
 * query records from current data source
 *
 * @param fields
 * @param table
 * @param where clause
 * @param group by
 * @param order by
 * @param offset
 * @param limit
 * @param arguments
 * @return array
 */
		static function query($f, $t, $w = "", $g = "", $o = "", $s = 0, $l = 0, $a = [])
		{
			//execute a query that returns records of associative arrays
			$q = "SELECT " . $f . ($t ? " FROM " . $t : "") . ($w ? " WHERE " . $w : "") . ($g ? " GROUP BY " . $g : "") . ($o ? " ORDER BY " . $o : "") . ($l ? (" LIMIT " . ($s ? $s . "," : "") . $l) : "") . ";";
			return self::exec($q, $a);
		}
/**
 * query one record from current data source
 *
 * @param fields
 * @param table
 * @param where clause
 * @param group by
 * @param order by
 * @param arguments
 * @return array
 */
		static function fetch($f, $t = "", $w = "", $g = "", $o = "", $a = [])
		{
			//return the first record
			$r = self::query($f, $t, $w, $g, $o, 0, 1, $a);
			return empty($r[ 0 ]) ? [] : $r[ 0 ];
		}
/**
 * query one field from current data source
 *
 * @param field
 * @param table
 * @param where clause
 * @param group by
 * @param order by
 * @param arguments
 * @return array
 */
		static function field($f, $t = "", $w = "", $g = "", $o = "", $a = [])
		{
			//return the first field
			return reset(self::fetch($f, $t, $w, $g, $o, $a));
		}
/**
 * query a recursive tree from current data source
 *
 * @param query string, use '?' placeholder to mark place of parent id
 * @param root id of the tree, 0 for all
 * @return array of data
 */
		static function tree($q, $p = 0)
		{
			//return a tree array (childs in _)
			$r = self::exec($q, [ $p ]);
			if(empty($r))
				return[];
			foreach($r as $k => $v) {
				$i = isset($v[ 'id' ]) ? $v[ 'id' ] : - 1;
				if(! empty($i) && $i != $p) {
					$c = self::tree($q, $i);
					if(! empty($c))
						$r[ $k ][ "_" ] = $c;
				}
			}
			return $r;
		}

/*** Templater ***/
/**
 * register a new stylesheet
 *
 * @param name of the stylesheet
 */
		static function css($c = "")
		{
			if(empty($c))
				return self::$core->css;
			//! add a new stylesheet to output
			$a = n(d(1));
			//! set path to extension root
			if(ia(m($a), [ "ctrl", "libs", "js", "css", "addons" ]))
				$a = n($a);
			$a .= "/css/" . @x("?", $c)[ 0 ];
			if(! f($a))
				$a .= ".php";
			if(! isset(self::$core->css[ $c ]) && f($a))
				self::$core->css[ $c ] = realpath($a);
		}
/**
 * register a new javascript library
 *
 * @param name of the js library
 * @param if it needs to be initialized, the code to do that
 */
		static function jslib($l = "", $i = "")
		{
			if(empty($l))
				return self::$core->jslib;
			//! add a new javascript library to output
			$a = n(d(1));
			//! set path to extension root
			if(ia(m($a), [ "ctrl", "libs", "js", "css", "addons" ]))
				$a = n($a);
			$a .= "/js/" . @x("?", $l)[ 0 ];
			if(! f($a))
				$a .= ".php";
			if(! isset(self::$core->jslib[ $l ]))
				self::$core->jslib[ $l ] = realpath($a);
			//! also register init hook and call it on onload event
			if(! empty($i) && (empty(self::$core->js[ "init()" ]) || strpos(self::$core->js[ "init()" ], r($i)) === false)) {
				$i = r($i);
				self::js("init()", $i . ($i[ u($i) - 1 ] != ";" ? ";" : ""), true);
			}
		}
/**
 * register a new javascript function
 *
 * @param name of the js function with arguments
 * @param code
 * @param if code should be appended to existing code, true. Replace otherwise
 */
		static function js($f = "", $c = "", $a = 0)
		{
			//! add a javascript function to output
			$C = minify($c, "js");
			$C .= ($C[ u($C) - 1 ] != ";" ? ";" : "");
			if($a) {
				if(! isset(self::$core->js[ $f ]))
					self::$core->js[ $f ] = "";
				if(strpos(self::$core->js[ $f ], $C) === false)
					self::$core->js[ $f ] .= $C;
			}
			else 
				self::$core->js[ $f ] = $C;
		}
/**
 * register a new menu item or submenu in PHPPE panel
 *
 * @param title of the link
 * @param url or array of title=>url
 */
		static function menu($t = "", $l = "")
		{
			//! add a new menuitem or submenu
			if(is_string($l) || a($l))
				self::$core->menu[ $t ] = $l;
		}
/**
 * picture manipulation
 *
 * @param original image file
 * @param new image file
 * @param maximum width
 * @param maximum height
 * @param crop image
 * @param use lossless compression (png), defaults to jpeg
 * @param watermark image, must be a semi-transparent png
 * @param maximum file size for output. Will reduce quality to fit
 * @param minimum quality (1-10)
 * @return boolean true or false, success
 */
		static function picture($o, $n, $w, $h, $c = 0, $l = 1, $W = "", $s = 8192, $m = 5)
		{
			//! try to load image, fallback to plain copy if failed
			$d = g($o);
			if(! y("gd_info") || empty($d) || ! ($i = @imagecreatefromstring($d))) {
				Core::log('W', "no php-libgd or bad image: $o", "picture");
				if(f($o))
					@copy($o, $n);
				return false;
			}
			//! get original image dimensions
			$x = imagesx($i);
			$y = imagesy($i);
			//! limit checks and output format
			$q = 9;
			$m = ($m > 0 && $m < 10 ? $m : 5);
			$s = ($s > 64 ? $s : 64) * 1024;
			$j = "imagepng";
			if(! $l) {
				$j = "imagejpeg";
				$m *= 10;
				$q = 99;
			}
			Core::log('D', $o . "($x,$y) -> " . $n . "($w,$h," . ($c ? "crop" : "resize") . ",$j,$s,$q) $W", "picture");
			//! calculate new picture dimensions
			if(! $c) {
				//! resize keeping aspect ratio
				$X = $x < $y ? floor(($h / $y) * $x) : $w;
				$Y = $x < $y ? $h : floor(($w / $x) * $y);
				$c = $d = 0;
				$e = $x;
				$f = $y;
			}
			else
			{
				//! crop from the middle
				$X = $w;
				$Y = $h;
				$c = $x > $y ? floor(abs($x - $y) / 2) : 0;
				$d = $x > $y ? 0 : floor(abs($y - $x) / 2);
				$e = $x > $y ? floor($w * $y / $h) : $x;
				$f = $x > $y ? $y : floor($w * $x / $h);
			}
			//! create output image
			$N = imagecreatetruecolor($X, $Y);
			//! don't loose transparent background
			if($l) {
				imagealphablending($N, 0);
				$a = imagecolorallocatealpha($N, 255, 255, 255, 255);
				imagesavealpha($N, 1);
			}
			else 
				$a = imagecolorallocate($N, 255, 255, 255);
			imagefill($N, 0, 0, $a);
			imagecopyresampled($N, $i, 0, 0, $c, $d, $X, $Y, $e, $f);
			//! tile watermark logo on image
			$T = "data/" . $W . ".png";
			if(! empty($W) && f($T)) {
				$g = imagecreatefrompng($T);
				$a = imagesx($g);
				$b = imagesy($g);
				for($y = 0; $y < $Y; $y += $a)
					for($x = 0; $x < $X; $x += $a)
					imagecopyresampled($N, $g, $x, $y, 0, 0, $a, $b, $a, $b);
			}
			//! reduce quality to match file maximum byte size requirement
			@unlink($n);
			while(! f($n) || (filesize($n) > $s && $q >= $m)) {
				@unlink($n);
				if(! $j($N, $n, $q-- )) {
					@copy($o, $n);
					return false;
				}
			}
			return true;
		}
/**
 * load, parse and evaluate a template
 *
 * @param name of the template
 * @return parsed output string
 */
		static function template($n)
		{
			//! set http response header as well for special templates
			if($n == "403" || $n == "404")
				header("HTTP/1.1 $n " . ($n == "403" ? "Access Denied" : "Not Found"));
			//get template content
			$d = self::_gt($n);
			if(empty($d))
				return "";
			self::$n = - 1;
			self::$c = [];			
			//parse tags
			return self::_t($d);
		}
/**
 * get value of a templater expression
 *
 * @param expression
 * @return value
 */
		static function getval($x)
		{
			if(! is_string($x))
				return $x;
			//! evaluate an expression for templater and return it's value
			//! security check: look for variables and let operator
			if(strpos($x, "$") !== false || p("/[^!=]=[^=]/", $x))
				return self::e("W", "BADINP", $x);
			$l = $r = "";
			$d = $x;
			//convert expression to php commands
			for($i = 0; $i < u($d); $i++ ) {
				$c = $d[ $i ];
				if($c == '"' || $c == "'") {
					$r .= $c;
					$i++ ;
					$b = "";
					while($i < u($d) && $b != $c) {
						if($b == "\\")
							$b .= $d[ ++ $i ];
						$r .= $b;
						$b = $d[ $i++ ];
					}
					$r .= $c;
					$l = "";
				}
				if((ctype_alpha($c) || $c == "_") && ! ctype_alnum($l) && $l != "." && $l != "_") {
					$j = $i;
					$b = $d[ $j ];
					while($b && (ctype_alnum($b) || $b == "_")) {
						$j++ ;
						$b = isset($d[ $j ]) ? $d[ $j ] : "";
					}
					if($b != "(" && $b != ":") {
						$v = z($d, $i, $j - $i);
						switch($v) {
							case "KEY" :
							case "IDX" :
							case "VALUE" :
							if(isset(self::$c[ self::$n ]->$v))
								$v = self::$c[ self::$n ]->$v;
							if(a($v))
								return $v;
							//! don't throw notice if non-scalar referenced by VALUE
							@$r .= "'" . ad($v) . "'";
							$i = $j;
							break;
							case "ODD" :
							$r .= self::$c[ self::$n ]->IDX % 2;
							$i = $j;
							break;
							case "parent" :
							$Y = self::$n - 1;
							while(z($d, $j, 7) == ".parent") {
								$j += 7;
								$Y-- ;
							}
							$n = z($d, $j + 1, 3);
							$j += 4 + ($n == "VAL" ? 2 : 0);
							if($n == "ODD")
								$v = self::$c[ $Y ]->IDX % 2;
							elseif(isset(self::$c[ $Y ]->$n))
								$v = self::$c[ $Y ]->$n;
							elseif(isset(self::$c[ $Y ]->VALUE->$n))
								$v = self::$c[ $Y ]->VALUE->$n;
							elseif(isset(self::$c[ $Y ]->VALUE[ $n ]))
								$v = self::$c[ $Y ]->VALUE[ $n ];
							else 
								$v = "";
							$r .= "'" . ad($v) . "'";
							$i = $j;
							break;
							case "true" :
							case "false" :
							case "null" :
							break;
							default : if(isset(self::$c[ self::$n ]) && a(self::$c[ self::$n ]->VALUE)) {
								@$r .= "'" . ad(self::$c[ self::$n ]->VALUE[ $v ]) . "'";
								$i = $j;
							}
							elseif(isset(self::$c[ self::$n ]->VALUE->$v)) {
								@$r .= "'" . ad(self::$c[ self::$n ]->VALUE->$v) . "'";
								$i = $j;
							}
							else
							{
								$r .= "\$";
								$f[ $v ] = 1;
							}
						}
					}
					elseif($b == "(" && ($j - $i != 1 || $d[ $i ] != "L") && z($d, $i, 5) != "core." && z($d, $i, $j - $i) != "array" && ! empty(self::$core->allowed) && ! ia(z($d, $i, $j - $i), self::$core->allowed))
						return self::e("E", "BADFNC", $x);
				}
				$r .= ($c == "." ? "->" : (isset($d[ $i ]) ? $d[ $i ] : ""));
				$l = $c;
			}
			//! for string operators
			$r = s($r, [ "+'" => ".'", "+\"" => ".\"", "'+" => "'.", "\"+" => "\"." ]);
			//! get application properties
			if(! empty($f)) {
				foreach($f as $k => $v) {
					if(! isset($$k) && isset(self::$o[ $k ]))
						$$k = self::$o[ $k ];
					elseif(! isset($$k) && isset(self::$o[ "app" ]->$k))
						$$k = self::$o[ "app" ]->$k;
				}
			}
			//! evaluate php
			try {
				$e = e();
				e($e &~ E_NOTICE);
				ob_start();
				$R = eval("return " . $r . ";");
				$o = o();
				e($e);
				if(self::$core->runlevel > 2 || ! empty($o))
					self::log(! empty($o) ? "E" : "D", $x . " => " . $r . " = " . serialize($R), "php");
			}
			catch(\Exception $E) {
				self::log("E", $E->getMessage() . " " . $r, "php");
			}
			return $R;
		}
/**
 * register an object in templater
 *
 * @param name
 * @param instance reference
 */
		static function assign($n, &$o)
		{
			self::$o[ $n ] = &$o;
		}

/*** private helper functions ***/
		//! application allowed to call them in special cases, but normally won't need any of them
		//! so "private" keyword left out on purpose
		static function _r()
		{
			//! save request uri, will be used later after successful login
			//! called when redirect has true as second argument.
			$_SESSION[ 'pe_r' ] = "http" . (self::$core->sec ? "s" : "") . "://" . $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
		}
		static function _cf($c)
		{
			//! check filters
			if(! a($c))
				$c = x(",", $c);
			if(! empty($c))
				foreach($c as $F) {
					$F = r($F);
					$G = C . "Filter\\$F";
					if(! empty($F) && (($F[ 0 ] == '@' && ! self::$user->has(w($F, 1))) || ($F[ 0 ] != '@' && ! @$G::filter()))) {
						return false;
					}
				}
			return true;
		}
		static function _tc()
		{
			//! return try counter
			return++ self::$tc;
		}
		static function _gt($n)
		{
			//! get a template in raw format
			$t = "";
			$m = [];
			$M = "meta";
			$V = "views";
			$e = ".tpl";
			//! from cache if possible
			if(! empty(self::$mc)) {
				$C = 't_' . sha1(self::$core->base . "_" . $n);
				$p = self::$mc->get($C);
				if(a($p)) {
					if(a($p[ 'm' ]))
						$m = $p[ 'm' ];
					if(! empty($p[ 'd' ]))
						$t = $p[ 'd' ];
				}
			}
			//on cache miss
			if(! $t) {
				//! from database - only for primary datasource
				if(! empty(self::$db[ 0 ]) && f(P . "sql/$V.sql")) {
					$d = self::$s;
					self::ds(0);
					try {
						foreach([ self::$core->app . "/" . $n, $n ] as $v) {
							$p = self::fetch("*", $V, "id=?", "", "", [ $v ]);
							if(! empty($p[ 'data' ])) {
								foreach([ "css", "jslib" ] as $c) {
									$t = jd($p[ $c ]);
									if(a($t))
										foreach($t as $v)
											$ {
												self::$core->$c}
									[ m($v) ] = $v;
								}
								$t = $p[ 'data' ];
								if(! empty($p[ $M ]))
									$m = jd($p[ $M ], 1);
								break;
							}
						}
					}
					catch(\Exception $e) {
					}
					self::ds($d);
				}
				//! from file - fallback if not found in database
				if(! $t)
					foreach([ "app/$V/$n$e", self::$p ? self::$p . "/$V/$n$e" : "", N . self::$core->app . "/$V/$n$e", P . "$V/$n$e" ] as $F)
						if($F && $t = g($F)) {
							while(p("/^[\r\n\ \t]*<$M name='([^']+)' content='([^']+)'>/m", $t, $r)) {
								$t = w($t, u($r[ 0 ]));
								$m[ $r[ 1 ] ] = $r[ 2 ];
							}
							break;
						}
				//! failsafe: remove comments and php tags
				$t = pr("/<!-.*?->[\r\n]*/ms", "", pr("/<\?.*?\?\>[\r\n]*/ms", "", $t));
				//! save to cache
				if(! empty(self::$mc) && ! empty($t))
					$this->_ms($C, [ "m" => $m, "d" => $t ]);
			}
			//! append meta tags
			self::$core->meta += a($m) ? $m : [ $m ];
			//! return raw template
			return $t;
		}
		static function _t($x, $re = 0)
		{
			//! parse a template string
			//check recursion limit
			$L = self::e("W", "TOOMNY", L("recursion limit exceeded"));
			if($re >= 64)
				return $L;
			//check if we're in cms edit mode
			$CM = self::$core->app == "cms" && self::$core->action == "pages" && q(C . "CMS", "icon");
			//get tags
			if(preg_match_all("/<!([^\[\-][^>]+)>[\r\n]*/ms", $x, $T, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
				//get opening/closing pairs
				$o = [];
				$I = 0;
				foreach($T as $k => $v) {
					$T[ $k ][ 0 ][ 2 ] = u($v[ 0 ][ 0 ]);
					$t = t(z($v[ 1 ][ 0 ], 0, 4));
					if(z($t, 0, 2) == "if" || $t == "fore" || $t == "temp")
						$o[ $I++ ] = $k;
					elseif($t == "else")
						$T[ $o[ $I - 1 ] ][ 4 ] = $k;
					elseif(z($t, 0, 3) == "/if" || $t == "/for" || $t == "/tem")
						$T[ $o[ -- $I ] ][ 3 ] = $k;
				}
				if($I)
					return self::e("W", "UNCLS", L("unclosed tag"));
				unset($o);
				//parse tags
				$C = 0;
				for($k = 0; $k < count($T) && $m = $T[ $k ]; $k++ ) {
					$ta = r($m[ 1 ][ 0 ]);
					$w = "";
					$a = "";
					if($ta[ 0 ] == "=")
						$t = "=";
					else 
						$t = t(strstr($ta, ' ', true));
					if(empty($t))
						$t = $ta;
					else 
						$a = r(w($ta, u($t)));
					$A = x(" ", $a);
					$oo = $m[ 0 ][ 1 ];
					$os = $m[ 0 ][ 2 ];
					//interpret tags
					switch($t) {
						//application output marker in frame. It's not our job to parse it
						case "app" :
						$w = "<!app>";
						break;
						//include another template
						case "include" :
						$c = self::_gt($a);
						if(! $c)
							$c = self::_gt(self::getval($a));
						$w = self::_t($c, $re + 1);
						break;
						//expression
						case "=" :
						$w = self::getval($a);
						break;
						//re-entrant parsing
						case "template" :
						$w = self::_t(s(self::_t(z($x, $m[ 0 ][ 1 ] + $m[ 0 ][ 2 ] + $C, $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ]), $re), "<%", "<!"), $re + 1);
						$k = $m[ 3 ];
						$os = $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ];
						break;
						//iteration
						case "foreach" :
						$d = self::getval($a);
						self::$n++ ;
						self::$c[ self::$n ] = (object)[ 'IDX' => 1 ];
						$t = z($x, $m[ 0 ][ 1 ] + $m[ 0 ][ 2 ] + $C, $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ]);
						if((a($d) && count($d) > 0) || is_object($d))
							foreach($d as $k => $v) {
								self::$c[ self::$n ]->KEY = $k;
								self::$c[ self::$n ]->VALUE = $v;
								$w .= self::_t($t, $re + 1);
								self::$c[ self::$n ]->IDX++ ;
							}
						$k = $m[ 3 ];
						$os = $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ];
						unset(self::$c[ self::$n ]);
						self::$n-- ;
						break;
						//conditional
						case "if" :
						$os = $T[ $m[ 3 ] ][ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ] - $m[ 0 ][ 1 ];
						$w = self::_t(($a != "cms" && ! empty(self::getval($a))) || ($a == "cms" && $CM) ? z($x, $oo + $C + $m[ 0 ][ 2 ], ! empty($m[ 4 ]) ? $T[ $m[ 4 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ] : $os - $m[ 0 ][ 2 ] - $T[ $m[ 3 ] ][ 0 ][ 2 ]) : (! empty($m[ 4 ]) ? z($x, $T[ $m[ 4 ] ][ 0 ][ 1 ] + $C + $T[ $m[ 4 ] ][ 0 ][ 2 ], $T[ $m[ 3 ] ][ 0 ][ 1 ] - $T[ $m[ 4 ] ][ 0 ][ 1 ] - $T[ $m[ 4 ] ][ 0 ][ 2 ]) : ""), $re + 1);
						$k = $m[ 3 ];
						break;
						//object reference
						case "form" :
						self::$tc = 0;
						$c = self::$core->app . "." . self::$core->action;
						$n = ! empty($A[ 0 ]) && $A[ 0 ] != "-" ? urlencode($A[ 0 ]) : "form";
						$w = "<form name='" . $n . "' action='" . url(! empty($A[ 1 ]) && $A[ 1 ] != "-" ? $A[ 1 ] : "") . "' method='post' enctype='multipart/form-data'" . (! empty($A[ 2 ]) && $A[ 2 ] != "-" ? " onsubmit=\"" . s($A[ 2 ], "\"", "\\\"") . "\"" : "") . "><input type='hidden' name='MAX_FILE_SIZE' value='" . self::$fm . "'><input type='hidden' name='pe_s' value='" . @$_SESSION[ "pe_s" ][ $c ] . "'><input type='hidden' name='pe_f' value='" . $n . "'>" . (! empty(self::$core->item) ? "<input type='hidden' name='item' value='" . h(self::$core->item) . "'>" : "");
						break;
						//date and time formating
						case "time" :
						case "date" :
						$v = self::getval($A[ 0 ]);
						$w = ! empty($v) ? date((! empty(self::$l[ 'dateformat' ]) ? self::$l[ 'dateformat' ] : "Y-m-d") . ($t == "time" ? " H:i:s" : ""), ts($v)) : (! empty($A[ 1 ]) ? "" : L("Epoch"));
						break;
						case "difftime" :
						$w = "";
						$v = self::getval($A[ 0 ]);
						if(! empty($A[ 1 ])) {
							if(! $v) {
								$w = "-";
								break;
							}
							$v -= ts(self::getval($A[ 1 ]));
						}
						if($v < 0) {
							$w = "- ";
							$v = - $v;
						}
						$c = floor($v / 86400);
						$b = floor(($v - $c * 86400) / 3600);
						$a = floor(($v - $c * 86400 - $b * 3600) / 60);
						$w .= $c ? "$c " . L("day" . ($c > 1 ? "s" : "")) : ($b ? "$b " . L("hour" . ($b > 1 ? "s" : "")) : "") . ($a || ! $b ? ($b ? ", " : "") . "$a " . L("min" . ($a > 1 ? "s" : "")) : "");
						break;
						//multilanguage support
						case "l" :
						$w = isset(self::$l[ $a ]) ? L($a) : "NA_" . $a;
						break;
						//dump object - this only works if runlevel is at least testing (1)
						case "dump" :
						$l = self::$core->runlevel;
						if($l < 1)
							$w = "";
						else
						{
							ob_start();
							$s = null;
							if($A[ 0 ] == "_SESSION") {
								$s = $_SESSION;
								unset($s[ "pe_u" ]);
								unset($s[ "pe_s" ]);
								unset($s[ "pe_v" ]);
							}
							else 
								$s = self::getval($A[ 0 ]);
							//use print_r in verbose, var_dump on developer and debug runlevels
							if($l > 1) {
								var_dump($s);
								$n = o();
								if($n[ 0 ] != "<")
									$n = "<pre>" . $n . "</pre>";
							}
							else
							{
								print_r($s);
								$n = "<pre>" . h(o()) . "</pre>";
							}
							$w = "<b style='font-family:monospace;'>" . $A[ 0 ] . ":</b>" . s($n, "<pre", "<pre style='margin:0;'");
						}
						break;
						//hook for cms editor icons
						case "cms" :
						{
							if($CM)
								$w = CMS::icon($A) . "<span>";
							//no break, fall into add-on code
						}
						//add-on support
						case "widget" :
						case "var" :
						case "field" :
						$Z = $R = $m = false;
						//if first attribute starts with an at sign, it's an ace definition
						if($A[ 0 ][ 0 ] == '@') {
							$Z = w($A[ 0 ], 1);
							array_shift($A);
						}
						//if type starts with an asterix, it's a mandatory field
						//equal sign does not show error on missing addon, but display plain value
						if($A[ 0 ][ 0 ] == '*') {
							$R = true;
							$A[ 0 ] = w($A[ 0 ], 1);
						}
						$f = $A[ 0 ];
						//get add-on type and arguments
						if(p("/^([^\(]+)[\(]?([^\)]*)/", $A[ 0 ], $ma) && ! empty($ma[ 1 ])) {
							$f = $ma[ 1 ];
							//submit is just an alias of update
							if($f == "submit")
								$f = "update";
							//get arguments array
							$a = self::getval("[" . $ma[ 2 ] . "]");
							array_shift($A);
							//name
							$n = ! empty($A[ 0 ]) ? $A[ 0 ] : "";
							array_shift($A);
							//value (if applicable)
							if(! ia($f, [ "update", "cancel", "button" ]))
								$v = self::getval($n);
							//find appropriate class for AddOn
							$d = A . $f;
							if(! ce($d) && $D = @glob(N . "*/addons/" . $f . PE)[ 0 ])
								io($D);
							if(ce($d) && is_subclass_of($d, C . "AddOn")) {
								//ok, got it
								$F = new $d($a, $n, $v, $A, $R);
								//if it has an init() method, and not called yet, call it
								if(empty(self::$core->addons[ $f ]) && q($F, "init"))
									$F->init();
								//add validators
								if($R || $f == "check" || $f == "file" || q(A . $f, "validate"))
									$_SESSION[ "pe_v" ][ $n ][ $f ] = [ $R, $a, $A ];
								//find out method to use to draw AddOn
								$m = $t != "cms" && ($t == "field" || ! empty($_SESSION[ $t == "var" ? "pe_e" : "pe_c" ])) && q($F, "edit") ? "edit" : "show";
								//get output
								$w .= q($F, $m) && (! $Z || self::$user->has($Z)) ? $F->$m() : $v;
								unset($F);
								break;
							}
						}
						$w .= $t == "cms" || ($t == "var" && empty($_SESSION[ 'pe_e' ])) ? (is_scalar($v) ? $v . "" : $v && json_encode($v)) : self::e("W", "UNKADDON", $f);
						$w .= $t == "cms" && $CM ? "</span>" : "";
						break;
						default : $w = self::e("W", "UNKTAG", $t);
					}
					//replace templater tag with output, not using any search and replace algorithm
					$D = $oo + $C && $x[ $oo + $C - 1 ] == "\n" ? 1 : 0;
					$E = isset($x[ $oo + $os + $C ]) && $x[ $oo + $os + $C ] == "\n" ? 1 : 0;
					$x = z($x, 0, $oo + $C - $D) . $w . w($x, $oo + $os + $C + $E);
					$C += @u($w) - $os - $D - $E;
				}
			}
			return $x;
		}
		//! memcache set
		function _ms($k, $v)
		{
			self::$mc->set($k, $v, MEMCACHE_COMPRESSED, $this->cachettl);
		}
		//! event handler
		function _eh($e, $c = [])
		{
			foreach($this->libs as $k => $v)
				if(q($v, $e))
					$c = $v->$e($c);
			return $c;
		}
	}
}

/*** Common routing filters ***/
namespace PHPPE\Filter {
	use \PHPPE\Filter as X;
	class get extends X
	{
		function filter()
		{
			return $_SERVER[ 'REQUEST_METHOD' ] == "GET";
		}
	}
	class post extends X
	{
		function filter()
		{
			return $_SERVER[ 'REQUEST_METHOD' ] == "POST";
		}
	}
	class loggedin extends X
	{
		function filter()
		{
			if(\PHPPE\Core::$user->id)
				return true;
			/* save request uri for returning after successful login */
			\PHPPE\Core::redirect("login", 1);
		}
	}
	class csrf extends X
	{
		function filter()
		{
			return \PHPPE\Core::isTry();
		}
	}
}

/*** Built-in fields ***/
namespace PHPPE\AddOn {
	use \PHPPE\Core as Core;
	use \PHPPE\AddOn as X;

/**
 * hidden field element
 *
 * @usage obj.field
 */
	class hidden extends X
	{
		function edit()
		{
			return "<input type='hidden' name='" . $this->fld . "' value='" . h(r($this->value)) . "'>";
		}
	}

/**
 * javascript button element
 *
 * @usage label onclickjs [cssclass]
 */
	class button extends X
	{
		function show()
		{
			return $this->edit();
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			return "<button class='" . (! empty($a[ 1 ]) && $a[ 1 ] != "-" ? $a[ 1 ] : "button") . "' onclick=\"" . s(! empty($a[ 0 ]) && $a[ 0 ] != "-" ? $a[ 0 ] : "alert('" . L("No action") . "');", "\"", "\\\"") . "\">" . L(! empty($t->name) ? $t->name : "Press me") . "</button>";
		}
	}

/**
 * form submit button element
 *
 * @usage [label [onclickjs [cssclass]]]
 */
	class update extends X
	{
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			return "<input class='" . (! empty($a[ 1 ]) && $a[ 1 ] != "-" ? $a[ 1 ] : "button") . "' name='pe_try" . Core::_tc() . "' type='submit' value=\"" . h(L(! empty($t->name) ? $t->name : "Okay")) . "\"" . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? " onclick=\"return " . s($a[ 0 ], "\"", "\\\"") . "\"" : "") . ">";
		}
	}

/**
 * cancel button element
 *
 * @usage [label [cssclass]]
 */
	class cancel extends X
	{
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			return "<input class='" . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? $a[ 0 ] : "button") . "' name='pe_cancel' type='submit' value=\"" . h(L(! empty($t->name) ? $t->name : "Cancel")) . "\">";
		}
	}

/**
 * text field element
 *
 * @usage (size[,maxlen[,rows]]) obj.field [onkeyupjs [cssclass [fakevalue]]]
 */
	class text extends X
	{
		function edit()
		{
			$t = $this;
			$a = $t->args;
			$v = r($t->value);
			if($v == "null")
				$v = "";
			if(! empty($a[ 2 ]) && $a[ 2 ] > 0) {
				if($a[ 1 ] > 0)
					Core::js("pe_mt(e,m)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;return(c==8||c==46||o.value.length<=m);");
				return "<textarea" . @v($t, $t->attrs[ 1 ], $t->attrs[ 0 ]) . ($a[ 0 ] > 0 ? " cols='" . $a[ 0 ] . "'" : "") . " rows='" . $a[ 2 ] . "'" . ($a[ 1 ] > 0 ? " onkeypress='return pe_mt(event," . $a[ 1 ] . ");'" : "") . " wrap='soft' onfocus='this.className=this.className.replace(\" errinput\",\"\")'>" . $v . "</textarea>";
			}
			return "<input" . @v($t, $t->attrs[ 1 ], $t->attrs[ 0 ], $a) . " type='text'" . (! empty($t->attrs[ 2 ]) && $t->attrs[ 2 ] != "-" ? " onkepup='" . $t->attrs[ 2 ] . "'" : "") . " onfocus='this.className=this.className.replace(\" errinput\",\"\")'" . (! empty($t->attrs[ 3 ]) ? " placeholder=\"" . h(r($t->attrs[ 3 ])) . "\"" : "") . " value=\"" . h($v) . "\">";
		}
	}

/**
 * password field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class pass extends X
	{
		function show()
		{
			return "******";
		}
		function edit()
		{
			$t = $this;
			return "<input" . @v($t, $t->attrs[ 0 ], "", $t->args) . " type='password' value=\"" . h(r($t->value)) . "\" onfocus='this.className=this.className.replace(\" errinput\",\"\")'>";
		}
		static function validate($n, &$v, $a, $t)
		{
			if(y(C . "pass"))
				return \PHPPE\pass($n, $v, $a, $t);
			$r = p("/[0-9]/", $v) && p("/[a-z]/i", $v) && t($v) != $v && k($v) != $v && u($v) >= 6;
			return[ $r, "not a valid password! [a-zA-Z0-9]*6" ];
		}
	}

/**
 * number element
 *
 * @usage (size[,maxlen[,min,max]]) obj.field [cssclass]
 */
	class num extends X
	{
		function show()
		{
			return h($this->value);
		}
		function edit()
		{
			$a = $this->args;
			$t = "this.value";
			$b = 'o.className=o.className.replace(" errinput","")';
			$C = isset($a[ 3 ]) ? "if($t<" . $a[ 2 ] . ")$t=" . $a[ 2 ] . ";if($t>" . $a[ 3 ] . ")$t=" . $a[ 3 ] . ";" : "";
			$r = "return";
			Core::js("pe_on(e)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;$b;if(c==8||c==37||c==39||c==46)$r true;c=String.fromCharCode(c);if(c.match(/[0-9\\b\\t\\r\\-\\.\\,]/)!=null)$r true;else{o.className+=' errinput';setTimeout(function(){{$b};},200);$r false;}");
			return "<input" . @v($this, $this->attrs[ 1 ], $this->attrs[ 0 ], $a) . " style='text-align:right;' type='number' onkeypress='$r pe_on(event);' onkeyup='$t=$t.replace(\",\",\".\");' onfocus='" . $C . "if($t==\"\")$t=0;" . s($b, "o.", "this.") . ";this.select();'" . (isset($a[ 3 ]) ? " onblur='$C' min='" . $a[ 2 ] . "' max='" . $a[ 3 ] . "'" : "") . " value=\"" . h(r($this->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			$r = floatval($v) == $v;
			//p("/^[0-9\-][0-9\.]+$/", $v);
			if(! $r)
				$m = "not a valid number!";
			if($r && isset($a[ 3 ])) {
				if($v < $a[ 2 ]) {
					$r = 0;
					$m = "not enough!";
					$v = $a[ 2 ];
				}
				if($v > $a[ 3 ]) {
					$r = 0;
					$m = "too much!";
					$v = $a[ 3 ];
				}
			}
			return[ $r, $m ];
		}
	}

/**
 * option list element
 *
 * @usage (size[,ismultiple]) obj.field options [skipids [onchangejs [cssclass]]]
 */
	class select extends X
	{
		function show()
		{
			return h(a($this->value) ? implode(", ", $this->value) : $this->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$b = $t->args;
			$opts = ! empty($a[ 0 ]) && $a[ 0 ] != "-" ? Core::getval($a[ 0 ]) : [];
			if(is_string($opts))
				$opts = x(",", $opts);
			$skip = ! empty($a[ 1 ]) && $a[ 1 ] != "-" ? Core::getval($a[ 1 ]) : [];
			if(is_string($skip))
				$skip = x(",", $skip);
			if(! a($skip))
				$skip = [];
			else 
				$skip = array_flip($skip);
			if(! empty($b[ 1 ]))
				$t->name .= "[]";
			$r = "<select" . @v($t, $a[ 3 ], $a[ 2 ]) . (! empty($b[ 1 ]) ? " multiple" : "") . (! empty($b[ 0 ]) && $b[ 0 ] > 0 ? " size='" . intval($b[ 0 ]) . "'" : "") . " onfocus='this.className=this.className.replace(\" errinput\",\"\")'>";
			if(a($opts))
				foreach($opts as $k => $v) {
					$o = a($v) && isset($v[ 'id' ]) ? $v[ 'id' ] : (is_object($v) && isset($v->id) ? $v->id : $k);
					$n = a($v) && ! empty($v[ 'name' ]) ? $v[ 'name' ] : (is_object($v) && ! empty($v->name) ? $v->name : (is_string($v) ? $v : $k));
					if(! isset($skip[ $o ]) && ! empty($n))
						$r .= "<option value=\"" . h($o) . "\"" . ((a($t->value) && ia($o . "", $t->value)) || $o == $t->value ? " selected" : "") . ">" . $n . "</option>";
					}
			$r .= "</select>";
			return $r;
		}
	}

/**
 * checkbox element
 *
 * @usage (truevalue) obj.field [label [cssclass]]
 */
	class check extends X
	{
		function show()
		{
			$t = $this;
			if(empty(Core::$core->output) || Core::$core->output != "html")
				return "[" . (! empty($t->value) ? "X" : " ") . "]" . (! empty($t->attrs[ 0 ]) ? L($t->attrs[ 0 ]) : $t->value);
			return h($t->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$e = Core::isError($t->name);
			return($e ? "<span class='errinput'>" : "") . "<input" . @v($t, $a[ 2 ], $a[ 1 ]) . " id='" . $t->name . "' type='checkbox'" . (! empty($t->value) ? " checked" : "") . " value=\"" . h(r(! empty($t->args[ 0 ]) ? $t->args[ 0 ] : "1")) . "\">" . (! empty($a[ 0 ]) ? "<label for='" . $t->name . "'>" . L($a[ 0 ]) . "</label>" : "") . ($e ? "</span>" : "");
		}
	}

/**
 * radiobutton elements
 *
 * @usage (value) obj.field [label [cssclass]]
 */
	class radio extends X
	{
		function show()
		{
			$t = $this;
			return Core::$core->output != "html" ? ("(" . ($t->value == $t->args[ 0 ] ? "X" : " ") . ") " . (! empty($t->attrs[ 0 ]) ? L($t->attrs[ 0 ]) : $t->value)) : h($t->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->args;
			$b = $t->attrs;
			return "<input" . @v($t, $b[ 2 ], $b[ 1 ]) . " id='" . $t->name . "_" . sha1($a[ 0 ]) . "' type='radio'" . ($t->value == $a[ 0 ] ? " checked" : "") . " value=\"" . h(r(! empty($a[ 0 ]) ? $a[ 0 ] : "1")) . "\">" . (! empty($b[ 0 ]) ? "<label for='" . $t->name . "_" . sha1($a[ 0 ]) . "'>" . L($b[ 0 ]) . "</label>" : "");
		}
	}

/**
 * phone number field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class phone extends X
	{
		function show()
		{
			return h($this->value);
		}
		function edit()
		{
			$t = $this;
			$b = 'o.className=o.className.replace(" errinput","")';
			Core::js("pe_op(e)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;$b;if(c==8||c==37||c==39||c==46)return true;c=String.fromCharCode(c);if(c.match(/[0-9\\b\\t\\r\\-\\ \\+\\(\\)\\/]/)!=null)return true;else{o.className+=' errinput';setTimeout(function(){{$b};},200);return false;}");
			return "<input" . @v($t, $t->attrs[ 1 ], $t->attrs[ 0 ], $t->args) . " type='text' onfocus='" . s($b, "o.", "this.") . "' onkeypress='return pe_op(event);' value=\"" . h(r($t->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			$r = p("/^[0-9\+][0-9\ \(\)\-]+$/", $v);
			return[ $r, "invalid phone number" ];
		}
	}

/**
 * email address field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class email extends X
	{
		function show()
		{
			if(empty(Core::$core->output) || Core::$core->output != "html")
				return $this->value;
			return s(h($this->value), [ "@" => "&#64;", "." => "&#46;" ]);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$b = 'o.className=o.className.replace(" errinput","")';
			Core::js("pe_oe(o)", "$b;if(o.value!=''&&o.value.match(/^.+\@(\[?)[a-z0-9\-\.]+\.([a-z]{2,3}|[0-9]{1,3})(\]?)$/i)==null)o.className+=' errinput';");
			return "<input" . @v($t, $a[ 1 ], "", $t->args) . " type='text' onfocus='" . s($b, "o.", "this.") . "' onchange='pe_oe(this);" . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? $a[ 0 ] : "") . "' value=\"" . h(r($t->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			$r = p("/^.+\@(\[?)[a-z0-9\-\.]+\.([a-z]{2,3}|[0-9]{1,3})(\]?)$/i", $v);
			return[ $r, "invalid email address" ];
		}
	}

/**
 * file upload input box
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class file extends X
	{
		function edit()
		{
			$t = $this;
			$e = Core::isError($t->name);
			return($e ? "<span class='errinput'>" : "") . "<input" . @v($t, $t->attrs[ 0 ], $t->attrs[ 1 ], $t->args) . " type='file'>&nbsp;(" . round(Core::$fm / 1048576) . "Mb)" . ($e ? "</span>" : "");
		}
	}

/**
 * date input elements. returns unix timestamp after validation
 *
 * @usage (yearsbefore[,yearsafter]) obj.field [cssclass]
 */
	class date extends X
	{
		function show()
		{
			return $this->show2(0);
		}
		function show2($T = 0)
		{
			$d = 'dateformat';
			$t = $this;
			return ! empty($t->value) ? date((! empty(Core::$l[ $d ]) ? Core::$l[ $d ] : "Y-m-d") . ($T ? " H:i" : ""), $t->value) : (! empty($t->attrs[ 0 ]) ? "" : "Epoch");
		}
		function edit()
		{
			return $this->edit2(0);
		}
		function edit2($T = 0)
		{
			$B = 'o.className=o.className.replace(" errinput,"")';
			Core::js("pe_cs(n,e)", "if(n!=null){n.className=n.className.replace(' errinput','');if(e!=null)n.className+=' errinput';}");
			Core::js("pe_cd(o,f)", "var d=new Date(Date.UTC(o[f+':y'].value,o[f+':m'].value-1,o[f+':d'].value,o[f+':h']?o[f+':h'].value:0,o[f+':i']?o[f+':i'].value:0,0));pe_cs(o[f+':y']);pe_cs(o[f+':m']);pe_cs(o[f+':d']);pe_cs(o[f+':h']);pe_cs(o[f+':i']);if(!d||d.getUTCDate()!=o[f+':d'].value){pe_cs(o[f+':y'],1);pe_cs(o[f+':m'],1);pe_cs(o[f+':d'],1);}");
			$t = $this;
			$a = $t->args;
			$b = $t->attrs;
			$n = $t->fld;
			$t->name = "";
			$s = "<select" . @v($t, $b[ 1 ]) . " onfocus='pe_cd(this.form,\"" . $n . "\");' onchange='pe_cd(this.form,\"" . $n . "\");" . (! empty($b[ 0 ]) && $b[ 0 ] != "-" ? $b[ 0 ] : "") . "' name='" . $n;
			$d = x(",", date("Y,m,d,H,i,s", ! empty($t->value) ? $t->value : Core::$core->now));
			$oy = $s . ":y'>";
			for($i = date("Y", Core::$core->now) - (! empty($a[ 0 ]) ? $a[ 0 ] : 5); $i <= date("Y", Core::$core->now) + (! empty($a[ 0 ]) ? $a[ 0 ] : 5); $i++ )
				$oy .= "<option value='" . $i . "'" . ($i == $d[ 0 ] ? " selected" : "") . ">" . $i . "</option>";
			$oy .= "</select>";
			$om = $s . ":m'>";
			for($i = 1; $i <= 12; $i++ ) {
				$X = "month" . ($i < 10 ? "0" : "") . $i;
				$om .= "<option value='" . $i . "'" . ($i == intval($d[ 1 ]) ? " selected" : "") . ">" . (! empty(Core::$l[ $X ]) ? Core::$l[ $X ] : ($i < 10 ? "0" : "") . $i) . "</option>";
			}
			$om .= "</select>";
			$od = $s . ":d'>";
			for($i = 1; $i <= 31; $i++ )
				$od .= "<option value='" . $i . "'" . ($i == intval($d[ 2 ]) ? " selected" : "") . ">" . ($i < 10 ? "0" : "") . $i . "</option>";
			$od .= "</select>";
			$f = (! empty(Core::$l[ "dateformat" ]) ? Core::$l[ "dateformat" ] : "Y-m-d") . "@";
			if($T) {
				$f .= " H:i" . (! empty($a[ 3 ]) ? ":s" : "");
				$oh = $s . ":h'>";
				for($i = 0; $i <= 23; $i++ )
					$oh .= "<option value='" . $i . "'" . ($i == intval($d[ 3 ]) ? " selected" : "") . ">" . (($i < 10 ? "0" : "") . $i) . "</option>";
				$oh .= "</select>";
				$oi = $s . ":i'>";
				for($i = 0; $i <= 59; $i += (! empty($a[ 2 ]) && $a[ 2 ] > 0 && $a[ 2 ] <= 30 ? $a[ 2 ] : 1))
					$oi .= "<option value='" . $i . "'" . ($i == intval($d[ 4 ]) ? " selected" : "") . ">" . (($i < 10 ? "0" : "") . $i) . "</option>";
				$oi .= "</select>";
				if(! empty($a[ 3 ])) {
					$os = $s . ":s'>";
					for($i = 0; $i <= 59; $i++ )
						$os .= "<option value='" . $i . "'" . ($i == intval($d[ 5 ]) ? " selected" : "") . ">" . (($i < 10 ? "0" : "") . $i) . "</option>";
					$os .= "</select>";
				}
				else 
					$os = "";
			}
			else 
				$oh = $oi = $os = "";
			if(Core::isInst("Core")) {
				@list($o, $d) = x("_", $n);
				$c = "&nbsp;<img src='images/cal.png' class='cal_icon' style='vertical-align:middle;' onclick='if(document.forms[\"" . $o . "\"])cal_open(this,document.forms[\"" . $o . "\"],\"" . $n . "\");' alt='[☰]'>";
			}
			else 
				$c = "";
			return s($f, [ "Y" => $oy, "m" => $om, "d" => $od, "H" => $oh, "i" => $oi, "s" => $os, "@" => $c ]);
		}
		static function validate($n, &$v, $a, $t)
		{
			return[ mktime(! empty($_REQUEST[ $v . ":h" ]) ? $_REQUEST[ $v . ":h" ] : 0, ! empty($_REQUEST[ $v . ":i" ]) ? $_REQUEST[ $v . ":i" ] : 0, ! empty($_REQUEST[ $v . ":s" ]) ? $_REQUEST[ $v . ":s" ] : 0, ! empty($_REQUEST[ $v . ":m" ]) ? $_REQUEST[ $v . ":m" ] : 0, ! empty($_REQUEST[ $v . ":d" ]) ? $_REQUEST[ $v . ":d" ] : 0, ! empty($_REQUEST[ $v . ":y" ]) ? $_REQUEST[ $v . ":y" ] : 0), "bad date" ];
		}
	}

/**
 * date and time selector element
 *
 * @usage (yearsbefore[,yearsafter[,minutes-step]]) obj.field [cssclass]
 */
	class time extends date
	{
		function show()
		{
			return parent::show2(1);
		}
		function edit()
		{
			return parent::edit2(1);
		}
		static function validate($n, &$v, $a, $t)
		{
			return parent::validate($n, $v, $a, $t);
		}
	}
}
namespace {

/*** I18N ***/
/**
 * Loads a new language dictionary into memory
 *
 * @param class name
 * @return merged dictionary
*/
	function LANG_INIT($c = "")
	{
		//initialize language dictionary for a class
		return \PHPPE\Core::langInit($c);
	}
/**
 * Translate a string or code to user's language
 *
 * @usage L('Someting']), L('extensions_installtxt')
 * @param string or code
 * @return translated text
 */
	function L($s)
	{
		//translate a string
		return isset(\PHPPE\Core::$l[ $s ]) ? \PHPPE\Core::$l[ $s ] : strtr($s, [ "_" => " " ]);
	}
/**
 * Returns permanent link with absolute path
 *
 * @param application
 * @param action
 * @return url
 */
	function url($m = NULL, $p = NULL)
	{
		//return permalink for an action
		return \PHPPE\Core::url($m, $p);
	}

/*** for compactness, widely used functions. This makes code a lot smaller ***/
	function a($a)
	{
		return is_array($a);
	}
	function h(&$s)
	{
		return htmlspecialchars($s);
	}
	function s($s, $f, $t = "")
	{
		return a($f) ? strtr($s, $f) : strtr($s, [ $f => $t ]);
	}
	function e($e = 0)
	{
		return error_reporting($e);
	}
	function f($f)
	{
		return file_exists($f);
	}
	function g($f)
	{
		return f($f) ? file_get_contents($f) : "";
	}
	function io($f)
	{
		return f($f) ? include_once($f) : null;
	}
	function t($s)
	{
		return strtolower($s);
	}
	function k($s)
	{
		return strtoupper($s);
	}
	function c($m)
	{
		header("Pragma:cache");
		header("Cache-Control:cache,public,max-age=" . \PHPPE\Core::$core->cachettl);
		header("Content-Type:$m;charset=utf-8");
		header("Connection:close");
	}
	function d($n)
	{
		return debug_backtrace()[ $n ][ 'file' ];
	}
	function r($s)
	{
		return trim($s);
	}
	function n($s)
	{
		return dirname($s);
	}
	function m($a)
	{
		return basename($a);
	}
	function p($p, &$s, &$r = null, $f = 0)
	{
		return preg_match($p, $s, $r, $f);
	}
	function pr($a, $b, $c)
	{
		return preg_replace($a, $b, $c);
	}
	function x($a, $b)
	{
		//! quote-safe explode
		$r = [];
		$q = '';
		for($l = $i = 0; $i <= u($b); $i++ ) {
			$c = @$b[ $i ];
			if($q) {
				if($c == $q)
					$q = "";
				if($c == '\\')
					$i++ ;
			}
			elseif($c == '"' || $c == "'")
				$q = $c;
			elseif($c == $a || $c == '') {
				while(@$b[ $l ] == $a)
					$l++ ;
				$r[] = z($b, $l, $i - $l);
				$l = $i + 1;
			}
		}
		return $r;
		//		return explode($a, $b);
	}
	function q($a, $b)
	{
		return method_exists($a, $b);
	}
	function y($a)
	{
		return function_exists($a);
	}
	function w(&$a, $b)
	{
		return substr($a, $b);
	}
	function z(&$a, $b, $c)
	{
		return substr($a, $b, $c);
	}
	function v($a, $b, $e = "", $f = [])
	{
		return " class='" . $a->css . (! empty($b) && $b != "-" ? " " . $b : "") . "'" . ($a->name ? " name='" . $a->fld . "'" : "") . ($e && $e != "-" ? " onchange='" . $e . "'" : "") . (! empty($f[ 0 ]) && $f[ 0 ] > 0 ? " size='" . $f[ 0 ] . "'" : "") . (! empty($f[ 1 ]) && $f[ 1 ] > 0 ? " maxlength='" . $f[ 1 ] . "'" : "");
	}
	function u($a)
	{
		return strlen($a);
	}
	function j($a, $b, $c = "")
	{
		return sha1($a . "|" . $b . "|" . $c);
	}
	function o()
	{
		return ob_get_clean();
	}
	function jd(&$a)
	{
		return @json_decode($a, 1);
	}
	function ad($a)
	{
		return addslashes($a);
	}
	function ia($a, $b)
	{
		return @in_array($a, $b);
	}
	function ce($c)
	{
		return class_exists($c);
	}
	function cc($c)
	{
		return ce($c) && is_subclass_of($c, C . "App");
	}
	function ts($v)
	{
		return p("|^[0-9]+$|", $v) ? $v : strtotime($v);
	}

/******* Bootstrap PHPPE *******/
	if(empty(\PHPPE\Core::$core))
		new \PHPPE\Core(! empty(debug_backtrace()));
	return \PHPPE\Core::$core;
}
?>