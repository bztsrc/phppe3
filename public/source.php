<?php
/*
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
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
 * @file public/source.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief PHPPE micro-framework's Core
 * @see https://raw.githubusercontent.com/bztsrc/phppe3/master/public/source.php
 *
 * This is the nicely formatted and commented source version of PHPPE Core
 *
 * NOTE on unit testing: redirects and methods with die() cannot be tested
 * with PHPUnit. This does not mean they're untested, simply there are many
 * cases which are checked through PHPPE\Http::get() and not directly called
 * from test classes, see vendor/phppe/Developer/tests. Also note that
 * Developer extension ships with it's own PHPUnit compatible unit tester
 * class, so you can run the tests without phpunit.phar too.
 */
namespace PHPPE {
	define("VERSION", "3.0.0");

/**
 * Extension interface. We declare it as a class because
 * implementing event handlers are optional
 */
	class Extension
	{
		//function diag(){}
		//function init($cfg){}
		//function route($app,$action){}
		//function view($output){}
		//function filter(){}
		//function cronX($retCode){}
		//function stat()
	}

/**
 * Add-On prototype
 */
	class AddOn
	{
		public $name;				//!< instance name
		public $args;				//!< arguments in pharenthesis after type
		public $fld;				//!< field name
		public $value;				//!< object field's value
		public $attrs;				//!< attributes, everything after the name in tag
		public $css;				//!< css class to use, input or reqinput, occasionally errinput added
/**
 * Constructor, do not try to override, use init() instead
 *
 * @param arguments, listed with pharenthesis after type in templates
 * @param name of the add-on
 * @param reference of object field
 * @param attributes, listed after field name in templates
 * @param required field flag
 * @return PHPHE\AddOn instance
 */
		final function __construct($a, $n, &$v, $t = [], $r = 0)
		{
			//! save arguments, name and attributes
			$this->args = $a;
			$this->name = $n;
			$this->fld = strtr($n, ["."=>"_"]);
			$this->value = $v;
			$this->attrs = $t;
			//! css class name reqinput for mandatory fields
			$this->css = ((! empty($r) ? "req" : "") . "input").(Core::isError($n)?" errinput":"");
		}
/**
 * Init method is called when needed but only once per page generation
 * constructor may be called several times depending on the template
 */
		//! function init($cfgarray)
		//! {
		//!   call \PHPPE\Core::addon() and specify your Add-On's details
		//!   \PHPPE\Core::jslib() to load javascripts and
		//!   \PHPPE\Core::css() for style sheets here
		//! }
/**
 * Field input or widget configuration form
 *
 * @return string output
 */
		//! function edit() {return "";}
/**
 * Display a field's value or show widget face
 *
 * @return string output
 */
		function show()
		{
			return htmlspecialchars($this->value);
		}
/**
 * Value validator, returns boolean and a failure reason
 *
 * @param name of value to valudate, for error reporting
 * @param reference to value
 * @param arguments
 * @param attributes
 * @return array(boolean,error message) if the first value is true, it's valid
 */
		//! static function validate( $name, &$value,$args,$attrs )
		//! {
		//!	  return [true, "Dummy validator that always pass"];
		//! }
	}

/**
 * Filter prototype
 */
	class Filter
	{
		//static function filter()
	}

/**
 * Client class, this is used to store client's information and session
 */
	class Client extends Extension
	{
		public $ip;				//!< remote ip address. Also valid if behind proxy or load balancer
		public $agent;			//!< client program
		public $user;			//!< user account (unix user on CLI, http auth user on web)
		public $tz;				//!< client's timezone
		public $lang;			//!< client's prefered language
		public $screen=[];		//!< screen dimensions
		public $geo=[];			//!< geo location data (filled in by a third party extension)

/**
 * Initialize event
 * 
 * @param configuration array
 * @return false if initialization failed
 */
		function init($cfg=[])
		{
			Core::$l = [];
			Core::$client=$this;
			View::assign("client",$this);
			//! set up client's prefered language
			$L = 'pe_l';
			$a = "";
			$d = [];
			//! get prefered language from browser or from environment
			if(empty($_SESSION[ $L ])) {
				$i = 'HTTP_ACCEPT_LANGUAGE';
				$d = explode(",", strtr(! empty($_SERVER[ $i ]) ? $_SERVER[ $i ] : (getenv('LANG') || "en"), ["/"=> ""]));
			}
			//! this can be overriden from url
			if(! empty($_REQUEST[ 'lang' ]))
				$d = [ strtr(trim($_REQUEST[ 'lang' ]), ["/"=>""]) ];
			//! look for valid language code
			//! only allow if language is defined in core or in app
			foreach($d as $v) {
				list($a) = explode(";", strtolower(str_replace("-","_",$v)));
				if(! empty($a) && 
					(file_exists("vendor/phppe/Core/lang/$a.php") ||
					 file_exists("app/lang/$a.php") || $a=="en")) {
					$_SESSION[ $L ] = $a;
					break;
				}
			}
			//! failsafe
			if(empty($_SESSION[ $L ]))
				$_SESSION[ $L ] = "en";
			$this->lang = $v = $_SESSION[ $L ];
			$i = explode("_", $v);
			//! set PHP locale for the language
			setlocale(LC_ALL, strtolower($i[ 0 ]) . "_" . strtoupper(! empty($i[ 1 ]) ? $i[ 1 ] : $i[ 0 ]) . ".UTF8");
			//! load dictionary for core
			Core::lang("Core");
			$L = "pe_tz";
			if(Core::$w) {
				//! Detect values for Web
				if(Core::$core->app == "index" && empty($_SESSION[ $L ]) && 
				   !isset($_REQUEST[ 'nojs' ]) && empty($_REQUEST[ 'cache' ])) {
					//! this is a small JavaScript page that shows up for the first time
					//! after collecting information it redirects user so fast, he won't
					//! notice a thing.
					// @codeCoverageIgnoreStart
					$c = L("Enable JavaScript");
					if(empty($_REQUEST[ 'n' ])) {
						$_SESSION[ 'pe_n' ] = sha1(rand());
						//! save redirection url
						Http::_r();
						$g = "getTimezoneOffset()";
						$d = "var d%=new Date();d%.setDate(1);d%.setMonth(@);d%=parseInt(d%.$g);";
						die("<html><script type='text/javascript'>var now=new Date();" . strtr($d, [ '%' => '1', '@' => '1' ]) . 
						strtr($d, [ '%' => '2', '@' => '7' ]) . 
						"txt=now.toString().replace(/[^\(]+\(([^\)]+)\)/,\"$1\");document.location.href=\"" . 
						$_SESSION[ "pe_r" ] . 
						(strpos($_SERVER[ 'REQUEST_URI' ], "?") === false ? "?" : "&") . 
						"n=" . $_SESSION[ 'pe_n' ] . "&t=\"+(-now.$g*60)+\"&d=\"+(d1==d2||(d1<d2&&d1==parseInt(now.$g))||(d1>d2&&d2==parseInt(now.$g))?\"1\":\"0\")+\"&w=\"+screen.availWidth+\"&h=\"+screen.availHeight;</script>$c</html>");
					}
					elseif($_REQUEST[ 'n' ] == $_SESSION[ 'pe_n' ]) {
						unset($_SESSION[ 'pe_n' ]);
						$_SESSION[ $L ] = timezone_name_from_abbr("", $_REQUEST[ 't' ] + 0, $_REQUEST[ 'd' ] + 0);
						$_SESSION[ 'pe_w' ] = floor($_REQUEST[ 'w' ]);
						$_SESSION[ 'pe_h' ] = floor($_REQUEST[ 'h' ]);
						Http::redirect();
					}
					else
						die($c);
					// @codeCoverageIgnoreEnd
				}
				//! get client's real ip address
				$d = 'HTTP_X_FORWARDED_FOR';
				$this->ip = $i = ! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : $_SERVER[ 'REMOTE_ADDR' ];
				$this->screen = ! empty($_SESSION[ 'pe_w' ]) ? [ $_SESSION[ 'pe_w' ], $_SESSION[ 'pe_h' ] ] : [ 0, 0 ];
				//! agent is user agent
				$d = 'HTTP_USER_AGENT';
				$this->agent = ! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : "browser";
				//! user is http auth user
				$d = 'PHP_AUTH_USER';
				$this->user = ! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : "";
			}
			else
			{
				//! detect values for CLI
				$T = getenv("TZ");
				//! this should be a symlink to something 
				//! like /usr/share/zoneinfo/Europe/London
				$d = explode("/", $T ? $T : @readlink("/etc/localtime"));
				$c = count($d);
				$_SESSION[ $L ] = $c > 1 ? $d[ $c - 2 ] . "/" . $d[ $c - 1 ] : "UTC";
				//! no IP for tty
				$this->ip = "CLI";
				//! query tty size. If you know a better, exec()-less way, let me know!!!
				$c = exec("tput cols") + 0;
				$d = exec("tput lines") + 0;
				$this->screen = [ $c < 1 ? 80 : $c, $d < 1 ? 25 : $d ];
				//! agent is a terminal
				$d = getenv("TERM");
				$this->agent = ! empty($d) ? $d : "term";
				//! user is standard unix user
				$d = getenv("USER");
				$this->user = ! empty($d) ? $d : "";
				Core::$core->noframe = 1;
			}
			//! set up client's timezone
			date_default_timezone_set($this->tz = ! empty($_SESSION[ $L ]) ? $_SESSION[ $L ] : "UTC");
		}

	}

/**
 *  Model that supports Object Relational Mapping 
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
			return DS::query("*", static ::$_table, $w ? $w : ($s ? "id=?" : ""), "", $o, 0, 0, is_array($s) ? $s : [ $s ] );
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
			$r = DS::fetch("*", static ::$_table, $w ? $w : "id=?", "", $o, is_array($i) ? $i : [ $i ? $i : $this->id ]);
			//update property values. FETCH_INTO not exactly what we want
			if($r) {
				foreach($this as $k => $v)
					if($k[ 0 ] != '_')
						$this->$k = is_string($r[ $k ]) && 
						($r[ $k ][ 0 ] == '{' || $r[ $k ][ 0 ] == '[') ? json_decode($r[ $k ], true) : $r[ $k ];
				return true;
			}
			return false;
		}

/**
 * Save the current object into database. May also alter $id property (and that only).
 *
 * @return true on success
 */
		final function save($f = 0)
		{
			if(empty(static ::$_table))
				throw new \Exception("no _table");
			$d = DS::db();
			if(empty($d))
				throw new \Exception("no ds");
			//build the arguments array
			$a = [];
			foreach($this as $k => $v)
				if($k[ 0 ] != '_' && ($f || $k != 'id') && $k != 'created')
					$a[ $k ] = is_scalar($v) ? $v : json_encode($v);
			if(! DS::exec(($this->id && ! $f ? 
				"UPDATE " . static ::$_table . " SET " . implode("=?,", array_keys($a)) . "=? WHERE id=" . $d->quote($this->id) : 
				"INSERT INTO " . static ::$_table . " (" . implode(",", array_keys($a)) . ") VALUES (?" . str_repeat(",?", count($a) - 1) . ")"), array_values($a)))
				return false;
			//save new id for inserts
			if(! $this->id)
				$this->id = $d->lastInsertId();
			//return id
			return $this->id;
		}
	}

/**
 * Default user class, will be extended by PHPPE Pack with Users class
 */
	class User extends Model
	{
		public $id = 0;					//!< only for Anonymous. Otherwise user id can be a string as well
		public $name = "Anonymous";		//!< user real name
		public $data = [];				//!< user preferences
		// protected static $_table = "users";	//! set table name. This should be in Users class!
		private $acl = [];				//!< Access Control List
		// private remote = [];			//!< remote server configuration, added run-time

/**
 * Check access for an access control entry
 *
 * @param access control entry or list (pipe separated string or array)
 * @return boolean true or false
 */
		final function has($l)
		{
			//check if at least one of the ACE match
			foreach(is_array($l) ? $l : explode("|", $l) as $a) {
				$a = trim($a);
				//is user logged in?
				//is superadmin with bypass priviledge?
				if(! empty($this->id) && ($this->id == - 1 || 
					$a == "loggedin" || 
					!empty($this->acl[ $a ]) || 
					!empty($this->acl[ "$a:" . Core::$core->item ])))
					return true;
			}
			return false;
		}

/**
 * Grant priviledge for a user
 *
 * @param access control entry or list (pipe separated string or array)
 */
		function grant($l)
		{
			foreach(is_array($l) ? $l : explode("|", $l) as $a) {
				$a = trim($a);
				if(! empty($this->id) && ! empty($a))
					$this->acl[ $a ] = true;
			}
		}

/**
 * Drop privileges, specific access control entry or the whole access control list
 *
 * @param access control entry or list (pipe separated string or array) or empty for dropping all
 */
		function clear($l = "")
		{
			if(empty($l))
				$this->acl = [];
			else
			{
				foreach(is_array($l) ? $l : explode("|", $l) as $a) {
					$a = trim($a);
					//! drop the ACE
					unset($this->acl[ $a ]);
					//! drop item specific ACEs as well
					foreach($this->acl as $k => $v)
						if(substr($k, 0, strlen($a) + 1) == $a . ":")
							unset($this->acl[ $k ]);
				}
			}
		}

/**
 * Initialize event
 * 
 * @param configuration array
 * @return false if initialization failed
 */
		function init($cfg)
		{
			$L = "pe_u";
			if(! empty($_SESSION[ $L ]) && is_object($_SESSION[ $L ]))
			{
				Core::$user = $_SESSION[ $L ];
				foreach(["id","name","data"] as $k)
					$this->$k=Core::$user->$k;
			} else
				Core::$user = $_SESSION[ $L ] = $this;
			View::assign("user",Core::$user);
	}

/**
 * Route event. We handle login/logout actions here
 * 
 * @param current application
 * @param current action
 */
		// @codeCoverageIgnoreStart
		function route($app,$action)
		{
			//! operation modes
			if(! empty(Core::$user->id)) {
				//! edit for updating records and conf for widget configuration
				foreach([ "edit", "conf" ] as $v) {
					if(isset($_REQUEST[ $v ]) && Core::$user->has($v)) {
						$_SESSION[ 'pe_' . substr($v, 0, 1) ] = ! empty($_REQUEST[ $v ]);
						Http::redirect();
					}
				}
			}
			//! handle hardwired admin login and logout before Users class get's a chance
			if($app == "login") {
				$A = "admin";
				if(Core::isTry() && ! empty($_REQUEST[ 'id' ]) && $_REQUEST[ 'id' ] == $A) {
					//don't accept password in GET parameter
					if(! empty(Core::$core->masterpasswd) && empty(Core::$user->id) &&
						password_verify($_POST[ 'pass' ], Core::$core->masterpasswd)) {
						Core::log("A", "Login " . L($A), "users");
						$_SESSION[ "pe_u" ]->id = - 1;
						$_SESSION[ "pe_u" ]->name = L($A);
						//! don't let Users class to log in admin, that's our job
						Http::redirect();
					} else
						Core::error(L("Bad username or password"));
				}
				//! if already logged in redirect to home
				if(Core::$user->id)
					Http::redirect("/");
				//! if not superadmin, let Users extension's controller handle
			}
			elseif($app == "logout") {
				$i = Core::$user->id;
				if($i) {
					Core::log("A", "Logout " . Core::$user->name, "users");
					//! hook Users class' method for non-admin user logouts
					if($i != - 1 && method_exists(Core::$user, "logout"))
						Core::$user->logout();
				}
				session_destroy();
				Http::redirect("/");
			}
		}
		// @codeCoverageIgnoreEnd
	}

/**
 * HTTP helpers 
 */
	class Http extends Extension
	{
		static $r;			//!< url routes

/**
 * Generate a permanent link (see also url())
 *
 * @param application
 * @param action
 */
		static function url($m = "", $p = "")
		{
			//! generate canonized permanent link
			$c = Core::$core->base; $A=Core::$core->app;
			$f = basename(__FILE__);
			if(empty($m) && ! empty($A))
				$m = $A;
			if(empty($p) && ! empty($A) && $A == $m)
				$p = Core::$core->action;
			$a = ($m != "/" ? ($m . $p != "indexaction" ? $m . "/" : "") . (! empty($p) && $p != "action" ? $p . "/" : "") : "");
			return "http" . (Core::$core->sec ? "s" : "") . "://" . $c . ($c[ strlen($c) - 1 ] != "/" ? "/" : "") . 
			($f != "index.php" ? $f . ($a ? "/" : "") : "") . $a;
		}

/**
 * Redirect user
 *
 * @param url to redirect to
 * @param boolean save current url before redirect so that it will be used next time
 */
		// @codeCoverageIgnoreStart
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
			$f = basename(__FILE__);
			header("Location:" . (! empty($u) ? (strpos($u, "://") ? $u : 
				"http" . (Core::$core->sec ? "s" : "") . "://" . 
				Core::$core->base . ($f != "index.php" ? $f . "/" : "") . ($u != "/" ? $u : "")) : 
				self::url() . Core::$core->item));
			exit();
		}
		// @codeCoverageIgnoreEnd

/**
 * Application allowed to call this in special cases, but normally won't need it.
 * This function saves current request uri in session for later redirection
 */
		static function _r()
		{
			//! save request uri, will be used later after successful login
			//! called when redirect has true as second argument.
			@$_SESSION[ 'pe_r' ] = "http" . (Core::$core->sec ? "s" : "") . "://" . $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
		}

/**
 * Generate http header with mime info for content
 * 
 * @param mime type of output
 * @param boolean, client side cache enabled
 */
		static function mime($m, $c=true)
		{
			//on cli this will most probably report an error
			//as usually cli action handlers have already echoed output by now
			if($c)
			{
				header("Pragma:cache");
				header("Cache-Control:cache,public,max-age=" . Core::$core->cachettl);
				header("Connection:close");
			} else {
				header("Pragma:no-cache");
				header("Cache-Control:no-cache,no-store,private,must-revalidate,max-age=0");
			}
			header("Content-Type:$m;charset=utf-8");
		}
/**
 * Query routing table
 * @usage route()
 * @return array of routing rules
 *
 * Register a new url route. This method can handle many different input formats
 * @usage route(...) call it from your initialization code, extension/99_init.php
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
				if(! is_array($f))
					$f = Core::x(",", $f);
				self::$r[ Core::h($u, $n, $a) ] = [ $u, $n, $a, $f ];
			}
			//! associative array
			elseif(is_array($u) && ! empty($u[ $U ]) && ! empty($u[ $N ])) {
				$f = ! empty($u[ $F ]) ? $u[ $F ] : [];
				$a = ! empty($u[ $A ]) ? $u[ $A ] : "";
				self::$r[ Core::h($u[ $U ], $u[ $N ], $a) ] = [ $u[ $U ], $u[ $N ], $a, is_array($f) ? $f : explode(",", $f) ];
			}
			//! mass import from an array
			elseif(is_array($u) && ! empty(current($u)[ 0 ])) {
				foreach($u as $v)
					self::$r[ Core::h($v[ 0 ], $v[ 1 ], (! empty($v[ 2 ]) ? $v[ 2 ] : "")) ] = $v;
			}
			//! from stdClass
			elseif(is_object($u) && ! empty($u->$U) && ! empty($u->$N)) {
				$f = ! empty($u->$F) ? $u->$F : [];
				$a = ! empty($u->$A) ? $u->$A : "";
				self::$r[ Core::h($u->$U, $u->$N, $a) ] = [ $u->$U, $u->$N, $a, is_array($f) ? $f : explode(",", $f) ];
			}
			else
				throw new \Exception("bad route: " . serialize($u));
			//! limit check
			// @codeCoverageIgnoreStart
			if(count(self::$r) >= 512)
				Core::log("C", "too many routes");
			// @codeCoverageIgnoreEnd
		}

/**
 * Get controller for an url
 *
 * @param application
 * @param action
 * @param url
 * @return [application,action,arguments]
 */
		static function urlMatch($app="",$ac="",$url="")
		{
			$X = [];
			if(empty($app))
			{
				//! url routing
				$w = 0;
				if(! empty(self::$r)) {
					//! check routes, best match policy
					uasort(self::$r, function($a, $b)
					{
						return strcmp($b[ 0 ], $a[ 0 ]);
					});
					//! check route patterns
					foreach(self::$r as $v) {
						//! if matches current url
						if(preg_match("!^" . strtr($v[ 0 ], ["!"=>""]) . "!i", $url, $X)) {
							//! check filter
							if(!empty($v[3]) && ! Core::cf($v[ 3 ])) { $w = 1; continue; }
							$w=0;
							//! chop off whole match (first index) from arguments
							array_shift($X);
							//! get class and method
							$app = $v[ 1 ];
							$ac = $v[2];
							break;
						}
					}
				}
				//! if there was a match but failed due to filters,
				//! set output to 403 Access Denied page
				if($w) $app = Core::$core->template = "403";
			}
			//! load detected values if no application given
			if(empty($app)) $app = Core::$core->app;
			if(empty($ac)) $ac=Core::$core->action;
			return [$app,$ac,$X];
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
			if($l > 7) return;
			//! parse url
			if(preg_match("/^([^\:]+)\:\/\/([^\/\:]+)\:?([0-9]*)(.*)$/", $u, $m)) {
				//! validation and default values
				$s = 0;
				if($m[ 1 ] != "http" && $m[ 1 ] != "https") return;
				if($m[ 1 ] == "https") { $s = 1; $m[ 2 ] = "ssl://" . $m[ 2 ]; }
				if($m[ 3 ] == "") $m[ 3 ] = ($m[ 1 ] == "http" ? 80 : 443);
				if($m[ 4 ] == "") $m[ 4 ] = "/";
				//! open socket
				$f = fsockopen($m[ 2 ], $m[ 3 ], $n, $e, $T);
				if(! $f) {
					// @codeCoverageIgnoreStart
					//log failure
					Core::log("E", "$u #$n $e", "http");
					//give it a fallback in case ssl transport not configured in php
					return($s && strpos($e, '"ssl"') ? file_get_contents($u,false,is_array($p)?stream_context_create([
						'http'=>['method'=>'POST',
						'header'=> 'Content-type: application/x-www-form-urlencoded',
						'content'=>http_build_query($p)]]):null) : "");
					// @codeCoverageIgnoreEnd
				}
				//! construct POST
				$P = is_array($p) ? http_build_query($p, "_") : "";
				//! send request
				//! we are using HTTP/1.0 on purpose so that we don't have to mess with chunked response
				$o = ($P ? "POST" : "GET") . " " . $m[ 4 ] . " HTTP/1.0\r\nHost: " . $m[ 2 ] . "\r\nAccept-Language: " . Core::$client->lang . ";q=0.8\r\n" . ($C ? "Cookie: " . http_build_query($C, "", ";") . "\r\n" : "") . ($P ? "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($P) . "\r\n" : "") . "Connection: close;\r\n\r\n" . $P;
				fwrite($f, $o);
				//! receive response
				$d = $H = $n = "";
				$h = "-";
				$t = 0;
				stream_set_timeout($f, $T);
				while(! feof($f) && trim($h) != "") {
					//! parse headers
					$h = trim((fgets($f, 4096)));
					if(! empty($h)) {
						$H = strtolower($h);
						if(substr($H, 0, 8) == "location")
							$n = trim(substr($h, 9));
						if(substr($H, 0, 12) == "content-type" && strpos($h, "text/"))
							$t = 1;
						//! follow cookie changes
						if(substr($H, 0, 10) == "set-cookie") {
							$c = Core::x("=", Core::x(";", trim(substr($h, 11)))[ 0 ]);
							//c[1] is undefined on nginx when clearing the cookie
							@ $C[ $c[ 0 ] ] = $c[ 1 ];
						}
					}
				}
				//! handle redirections
				if($n && $n != $u)
					return self::get($n, $p, $T, $l + 1);
				//! receive data if there was a header (not timed out)
				if($H) {
					while(! feof($f))
						$d .= fread($f, 65535);
					Core::log("D", "$u " . strlen($d), "http");
				}
				// @codeCoverageIgnoreStart
				else
					Core::log("E", "$u timed out $T", "http");
				// @codeCoverageIgnoreEnd
				fclose($f);
				return $t ? strtr($d, ["\r"=>""]) : $d;
			}
		}

	}

/**
 *  DataSource layer. It's called DS and not DB because class DB is
 *  the Sql Query Builder shipped with Pack.
 */
	class DS extends Extension
	{
		public $name="";
		private static $db=[];		//!< database layer
		private static $s=0;		//!< data source selector
		private static $b=0;		//!< time consumed by data source queries (bill for db)

/**
 * Initialize primary datasource. Called by core
 * 
 * @param primary datasource uri
 */
		function __construct($ds=null)
		{
			//! initialize primary datasource if configured prior module initialization
			if(! empty($ds)) {
				//! replace string $this->db with an array of pdo objects
				@self::db($ds);
				//get primary datasource's name
				if(!empty(self::$db[0]))
					$this->name = self::$db[0]->name;
				//! get current timestamp from primary datasoure
				//! this will override time() in $core->now with
				//! a time in database server's timezone. This is
				//! important if webserver and dbserver are on
				//! separate hosts without time synchronization.
				try {
					$t = @strtotime(@self::field("CURRENT_TIMESTAMP"));
					if($t > 0)
						Core::$core->now = $t;
				// @codeCoverageIgnoreStart
				} catch(\Exception $e) {}
				// @codeCoverageIgnoreEnd
			}
		}

/**
 * Close database connections for all datasources
 */
		static function close()
		{
			if(! empty(self::$db))
				foreach(self::$db as $d)
					if(method_exists($d, "close"))
						$d->close();
			self::$db=[];
			self::$s=0;
		}

/**
 * Diag event. Look for sql updates
 */
		function diag()
		{
			//! nothing to do without database
			$d=@self::$db[0];
			if(empty($d))
				return;
			//! apply sql updates
			$D=[];
			foreach($d?["",".".$d->name]:[""] as $s)
				$D+=array_fill_keys(@glob("vendor/phppe/*/sql/upd_*".$s.".sql"),0);
			if(count($D))
				echo("DIAG-I: db update\n");
			foreach($D as $f=>$v) {
				//! get sql commands from file
				$s = Core::x(";",file_get_contents($f));
				@unlink($f);
				//! execute one by one
				foreach($s as $q)
					DS::exec($q);
			}
		}

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
			//! query PDO instance
			if(empty($u))
				return !empty(self::$db[ self::$s ])?self::$db[ self::$s ]:null;
			//! initialize a database and make connection available as a data source
			$S = microtime(1);
			//! create instance
			try {
				//! get username and password if it's not part of dsn
				if(! preg_match("/^(.*)@([^@:]+)?:?([^:]*)$/", $u, $d))
					$d[ 1 ] = $u;
				self::$s = count(self::$db);
				self::$db[] = is_object($O) ? $O : new \PDO($d[ 1 ], 
					! empty($d[ 2 ]) ? $d[ 2 ] : "",
					! empty($d[ 3 ]) ? $d[ 3 ] : "", 
					[ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => 0 ]);
				if(! isset(self::$db[ self::$s ])) throw new \PDOException();
				//! housekeeping
				$d = &self::$db[ self::$s ];
				$d->id = count(self::$db);
				$d->name = is_object($O) ? get_class($O) : $d->getAttribute(\PDO::ATTR_DRIVER_NAME);
				//! to maintain interoperability among different sql implementations, load replacements from
				//!   vendor/phppe/*/libs/ds_(driver).php
				$d->s = @include(@glob("vendor/phppe/*/libs/ds_" . $d->name . ".php")[ 0 ]);
				if(!empty($d->s["_init"])) {
					// @codeCoverageIgnoreStart
					//! driver specific commands for connection
					$c = Core::x(";", $d->s["_init"]);
					foreach($c as $n => $C)
						if(! empty(trim($C)))
							$d->exec(trim($C));
					// @codeCoverageIgnoreEnd
				}
			}
			catch(\Exception $e) {
				//! consider failure of first data source fatal
				Core::log(self::$s ? "E" : "C", L("Unable to initialize").": $u, " . $e->getMessage(), "db");
				throw $e;
			}
			self::$b += microtime(1) - $S;
			//! return selector of newly created instance
			return self::$s;
		}

/**
 * Set current data source to use with exec, fetch etc. if argument given
 *
 * @param data source selector
 * @return returns current selector
 */
		static function ds($s = - 1)
		{
			//! select a data source to use
			if($s >= 0 && $s < count(self::$db) && ! empty(self::$db[ $s ]))
				self::$s = $s;
			return self::$s;
		}
/**
 * Convert a string from user into a sql like phrase
 *
 * @param string
 * @return like string
 */
		static function like($s)
		{
			return
				preg_replace("/[%_]+/", "%", "%" .
				preg_replace("/[^a-z0-9\%]/i", "_",
				preg_replace("/[\ \t]+/", "%",
				strtr(trim($s), ["%"=>""]))) . "%");
		}

/**
 * Common code for executing a query on current data source. All the other methods are wrappers only
 *
 * @param query string
 * @return number of affected rows or data array
 */
		static function exec($q, $a = [])
		{
			//! log query in developer mode
			Core::log("D", $q . " " . json_encode($a), "db");
			//! check for valid datasource
			if(!is_array($a))
				$a = [ $a ];
//			if(empty($a[0]))
//				$a=[];
			if(empty(self::$db[ self::$s ]))
				throw new \Exception(L("Invalid ds") . " #" . self::$s);
			//! skip comment lines and empty queries by
			//! reporting 1 affected row to avoid errors on caller side
			$q = trim($q);
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
				//!   vendor/phppe/*/libs/ds_(driver).php
				if(is_array($h->s))
					foreach($h->s as $k => $v)
						if($k[0]!="_")
							$q = preg_replace("/".$k."/ims", $v, $q);
				$i = trim(strtolower(substr($q, 0, 6))) == "select";
				//! prepare and execute the statement with arguments
				$s = $h->prepare($q);
				$s->execute($a);
				//! get result, either an array or a number
				$r = $i ? $s->fetchAll(\PDO::FETCH_ASSOC) : $s->rowCount();
			}
			catch(\Exception $e) {
				//! try to load scheme for missing table
				$E = $e->getMessage(); $c=strtr($E, "le or v", "");
				// @codeCoverageIgnoreStart
				if((/* other */(!empty($h->s["_tablename"])&&preg_match($h->s["_tablename"],$E,$d)) ||
					/*Sqlite/MySQL/MariaDB*/ preg_match("/able:?\ [\'\"]?([a-z0-9_\.]+)/mi", $c, $d) ||
					/*Postgre*/ preg_match("/([a-z0-9_\.]+)[\'\"] does\ ?n/mi", $E, $d) || 
					/*MSSql*/ preg_match("/name:?\ [\'\"]?([a-z0-9_\.]+)/mi", $E, $d)
					) && ! empty($d[ 1 ])) {
				// @codeCoverageIgnoreEnd
					$c = "";
					$m = "." . trim($h->name);
					$d = explode(".", $d[ 1 ]);
					$d = trim(! empty($d[ 1 ]) ? $d[ 1 ] : $d[ 0 ]);
					list($D) = explode("_", $d);
					//! look for engine specific scheme
					$f = @glob("vendor/phppe/*/sql/" . $d . $m . ".sql")[ 0 ];
					//! common scheme
					if(empty($f))
						$f = @glob("vendor/phppe/*/sql/" . $d . ".sql")[ 0 ];
					if(!empty($f) && file_exists($f))
						$c=file_get_contents($f);
					//! if scheme not found
					if(empty($c)) {
						Core::log("E", $E, "db");
						throw $e;
					}
					//! execute schema creation commands
					$c = Core::x(";", $c);
					foreach($c as $n => $C) {
						try {
							if(! empty(trim($C)))
								$h->exec(trim($C));
						}
						catch(\Exception $e) {
							Core::log("E", "creating $d at line:" . ($n + 1) . " " . $e->getMessage(), "db");
							throw $e;
						}
					}
					Core::log("A", "$d created.", "db");
					//! repeat original command
					$s = $h->prepare($q);
					$s->execute($a);
					$r = $i ? $s->fetchAll(\PDO::FETCH_ASSOC) : $s->rowCount();
				}
				else
				{
					Core::log("E", $q . " " . json_encode($a) . " " . $E, "db");
					$r = null;
					throw $e;
				}
			}
			//! housekeeping
			self::$b += microtime(1) - $t;
			return $r;
		}

/**
 * Query records from current data source
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
			//! execute a query that returns records of associative arrays
			$q = "SELECT " . $f . ($t ? " FROM " . $t : "") . ($w ? " WHERE " . $w : "") . ($g ? " GROUP BY " . $g : "") . ($o ? " ORDER BY " . $o : "") . ($l ? (" LIMIT " . ($s ? $s . "," : "") . $l) : "") . ";";
			return self::exec($q, $a);
		}

/**
 * Query one record from current data source
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
			//! return the first record
			$r = self::query($f, $t, $w, $g, $o, 0, 1, $a);
			return empty($r[ 0 ]) ? [] : $r[ 0 ];
		}
/**
 * Query one field from current data source
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
			//! return the first field
			return @reset(self::fetch($f, $t, $w, $g, $o, $a));
		}

/**
 * Query a recursive tree from current data source
 *
 * @param query string, use '?' placeholder to mark place of parent id
 * @param root id of the tree, 0 for all
 * @return array of data
 */
		static function tree($q, $p = 0)
		{
			//! return a tree array (childs in _)
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

/**
 * Return time consumed by database calls
 *
 * @return secs
 */
		static function bill()
		{
			return self::$b;
		}
	}

/**
 * Cache wrapper. This allow multiple options
 * and fallbacks to php memcached
 */
	class Cache extends Extension
	{
		public $name;				//!< implementation
		static $mc;					//!< memcache instance

/**
 * Constructor. Called by core.
 * @usage configure it in vendor/phppe/Core/config.php
 * 
 * @param cache uri
 */
		function __construct($cfg=null)
		{
			if(! empty($cfg)) {
				$m = explode(":", $cfg);
				foreach([$m[0],ucfirst($m[0]),strtoupper($m[0])] as $M)
				{
					$d="\\PHPPE\\Cache\\".$M;
					if(class_exists($d))
					{
						self::$mc=new $d($cfg);
						break;
					}
				}
				//! if none, fallback to memcached
				if(! is_object(self::$mc)) {
				// @codeCoverageIgnoreStart
					//! unix file: "unix:/tmp/fifo", "host" or "host:port" otherwise
					if($m[ 0 ] == "unix") {
						$p = 0;
						$h = $m[ 1 ];
					}
					else
					{
						$p = $m[ 1 ] + 0;
						$h = $m[ 0 ];
					}
					$M = "\\Memcache";
					if(!class_exists($M))
						Core::log('E', "unable to initialize cache or no php-memcached", "cache");
					else {
						self::$mc = new $M;
						//Core::$mc->addServer( $h, $p );
						//$s = @Core::$mc->getExtendedStats(  );
						if(/*empty( $s[ $h . ( $p > 0 ? ":" . $p : "" ) ] ) || */ ! @self::$mc->pconnect($h, $p, 1)) {
							usleep(100);
							if(! @self::$mc->pconnect($h, $p, 1))
								self::$mc = null;
						}
					}
				}
				//! let rest of the world know about us
				if(is_object(self::$mc))
					$this->name=$M;
			}
			//! built-in blobs - referenced as cached objects
			//! serve them as soon as possible to speed up page load
			if(! empty($_GET[ "cache" ])) {
				$d = trim($_GET[ "cache" ]);
				switch($d) {
					case "logo" :
						Http::mime("image/png");
						$c = "vendor/phppe/Core/images/.phppe";
						die(file_exists($c) ? file_get_contents($c) :
						base64_decode("R0lGODlhKgAYAMIHAAACAAcAABYAAygBDD4BEFwAGGoBGwWYISH5BAEKAAcALAAAAAAqABgAAAOxeLrcCsDJSSkIoertYOSgBmXh5p3MiT4qJGIw9h3BFZP0LVceU0c91sy1uMwkwQfmYEzhiCwc8sh0QQ+FrMFQIAgY2cIWuUx9LoutWsxNs9udaxDKDb+7Wzth+huRRmlcJANrW148NjJDdF2Db2t7EzUUkwpqAx8EaoWRUyCXgVx5L1QUeQQDBGwFhIYDAxNNHJubBQqPBiWmeWqdWG+6EmrBxJZwxbqjyMnHy87P0BMJADs="));
					case "css" :
						Http::mime("text/css");
						$p = "position:fixed;top:";
						$s = "text-shadow:2px 2px 2px #FFF;";
						$c = "rgba(136,146,191";
						die("#pe_p{" . $p . "0;z-index:999;left:0;width:100%;padding:0 2px 0 32px;background-color:$c,0.9);background:linear-gradient($c,0.4),$c,0.6),$c,0.8),$c,0.9),$c,1) 90%,rgba(0,0,0,1));height:31px !important;font-family:helvetica;font-size:14px !important;line-height:20px !important;}#pe_p SPAN{margin:0 5px 0 0;cursor:pointer;}#pe_p UL{list-style-type:none;margin:3px;padding:0;}#pe_p IMG{border:0;vertical-align:middle;padding-right:4px;}#pe_p A{text-decoration:none;color:#000;" . $s . "}#pe_p .menu {position:fixed;top:8px;left:90px;}#pe_p .stat SPAN{display:inline-block;" . $s . "}#pe_p LI{cursor:pointer;}#pe_p LI:hover{background:#F0F0F0;}#pe_p .stat{" . $p . "6px;right:48px;}#pe_p .sub{" . $p . "28px;display:inline;background:#FFF;border:solid 1px #808080;box-shadow:2px 2px 6px #000;z-index:1000;}#pe_p .menu_i{padding:5px 6px 5px 6px;" . $s . "}#pe_p .menu_a{padding:4px 5px 5px 5px;border-top:solid #000 1px;border-left:solid #000 1px;border-right:solid #000 1px;background:#FFF;}@media print{#pe_p{display:none;}}");
					default :
						//! serve real cache requests
						$c = self::get("c_$d");
						if(is_array($c) && ! empty($c[ 'd' ])) {
							Http::mime((! empty($c[ 'm' ]) ? $c[ 'm' ] : "text/plain"));
							die($c[ 'd' ]);
						}
						die("CACHE-E: " . $d);
				}
			}
			// @codeCoverageIgnoreEnd

	}

/**
 * Set a value in cache
 *
 * @param key
 * @param value
 * @param ttl, optional
 */
	static function set($k, $v, $ttl=0)
	{
		if(!empty(self::$mc) && empty(Core::$core->nocache))
			return @self::$mc->set($k, $v, MEMCACHE_COMPRESSED, $ttl>0?$ttl:Core::$core->cachettl);
		return false;
	}

/**
 * Get a value from cache
 *
 * @param key
 */
	static function get($k)
	{
		if(!empty(self::$mc) && empty(Core::$core->nocache))
			return self::$mc->get($k);
		return null;
	}

/**
 * Initialize event
 * 
 * @param configuration array
 * @return false if initialization failed
 */
		function init($cfg)
		{
			//! remove Cache from extensions if there's no instance
			return(! empty(self::$mc));
		}

	}

/**
 * Assets proxy. It will use memcache if configured
 * Also takes care of dynamic assets and saves their output
 */
	class Assets extends Extension
	{

/**
 * Route event handler. Will look for images, css, js application
 *
 * @param current application
 * @param current action
 */
		// @codeCoverageIgnoreStart
		function route($app,$action)
		{
			//! proxy dynamic assets (vendor directory is not accessable by the webserver, only public dir)
			if(in_array($app, [ "css", "js", "images" ])) {
				//! helper function to specify mime header and minify assets
				function b($a, $b)
				{
					Http::mime($a == "css" ? "text/css" : ($a == "js" ? "text/javascript" : "image/png"));
					die(Assets::minify($b, $a));
				}
				//! let's try to get it from cache
				$N = 'a_' . sha1(url() . Core::$user->id . "/". Core::$client->lang);
				$d = Cache::get($N);
				if(!empty($d))
					b($app, $d);
				else
				{
					//! cache miss, we'll have to generate the asset
					//! remove language code from core.js url. This "alias" allows per language cache
					foreach([ Core::$core->url, 
							preg_replace("/^core\.[^\.]+\.js/", "core.js", Core::$core->url) . ".php" ] as $p) {
						$A = "vendor/phppe/*/" . strtr($p, [ "*" => "", ".." => "" ]);
						$c = @glob($A, GLOB_NOSORT)[ 0 ];
						if(empty($c))
						{
							$A = "public/" . strtr($p, [ "*" => "", ".." => "" ]);
							$c = @glob($A, GLOB_NOSORT)[ 0 ];
						}
						if($c) {
							if(substr($c, - 4) != ".php") {
								//! use file_get_contents and 10 times longer cache ttl for static files
								$d = file_get_contents($c);
								Core::$core->cachettl*=10;
							}
							else
							{
								//! use include_once for php with normal cache ttl
								ob_start();
								include_once($c);
								$d = ob_get_clean();
							}
						}
						if($d) {
							//! save it to the cache for later
							Cache::set($N, $d);
							//! output result
							b($app, $d);
						}
					}
				}
				//! no asset found by that url
				header("HTTP/1.1 404 Not Found");
				die;
			}
		}
		// @codeCoverageIgnoreEnd

/**
 * Asset minifier
 *
 * @param data
 * @param type, 'css' or 'js'
 */
		static function minify( $d, $t = "js" ) {
			//! check input, return output just as is if type unknown
			if(!empty(Core::$core->nominify) || ($t != "css" && $t != "js" && $t != "php"))
				return $d;
			$d=trim($d);
		
			//! allow use of third party vendor code
			if($t == "css" && class_exists("CSSMin") ) return \CSSMin::minify($d);
			if($t == "js" && class_exists("JSMin") ) return \JSMin::minify($d);
		
			//! do the stuff ourself (fastest, safest, simpliest, and no dependency required at all...)
			$n = ""; $i=0; $l=strlen($d);
			while($i<$l)
			{
				//! string literals
				if($d[$i]=="'" || $d[$i]=='"')
				{
					$s=$d[$i];$j=$i;$i++;
					while($i<$l && $d[$i]!=$s)
					{
						if($d[$i]=="\\") $i++;
						$i++;
					}
					$i++;
					$n.=substr($d,$j,$i-$j);
					continue;
				}
				//! remove comments
				if($t!="css" && ($d[$i]=='/' && $d[$i+1]=='/')) {
					$s=$i; $i+=2;
					while($i<$l && $d[$i]!="\n") $i++;
					continue;
				}
				if($d[$i]=='/' && $d[$i+1]=='*') {
					$s=$i; $i+=2;
					while($i+1<$l && ($d[$i]!='*' || $d[$i+1]!='/')) $i++;
					$i+=2;
					continue;
				}
				$c=substr($n,-1);
				//! remove tabs and line endings
				if($d[$i]=="\t" || $d[$i]=="\r" || $d[$i]=="\n")
				{
					//! add a space to separate words if necessary
					if(
						(($c>='a'&&$c<='z') || ($c>='A'&&$c<='Z')) &&
						($d[$i+1]=="\\" || $d[$i+1]=="/" || $d[$i+1]=="_" || ($d[$i+1]>='a'&&$d[$i+1]<='z') || ($d[$i+1]>='A'&&$d[$i+1]<='Z') || ($d[$i+1]>='0'&&$d[$i+1]<='9') || $d[$i+1]=='#')
					)
					$n.=" ";
					$i++;
					continue;
				}
				//! remove extra spaces
				if($d[$i]==" " &&
					(!(($c>='a'&&$c<='z') || ($c>='A'&&$c<='Z')) ||
					!($d[$i+1]=="\\" || $d[$i+1]=="/" || $d[$i+1]=="_" || ($d[$i+1]>='a'&&$d[$i+1]<='z') || ($d[$i+1]>='A'&&$d[$i+1]<='Z') || ($d[$i+1]>='0'&&$d[$i+1]<='9') || $d[$i+1]=='#')))
				{
					$i++;
					continue;
				}
				//! copy character to new string
				$n.=$d[$i++];
			}
			return $n;
		}

	}

/**
 * Content Server. This is the default fallback application if
 * url route failed
 */
	class Content extends Extension
	{
		function __construct()
		{
			//! check cache
			$C = 'd_' . sha1(url()."/".Core::$user->id."/".Core::$client->lang);
			$data = Cache::get($C);
			try {
				//! cache miss, look it up in database - only primary datasource
				if(empty($data['id']))
				{
					DS::ds(0);
					$data = DS::fetch("*", "pages",
						"(id=? OR ? LIKE id||'/%') AND ".
						"(lang='' OR lang=?) AND ".
						"pubd<=CURRENT_TIMESTAMP AND (expd=0 OR expd>CURRENT_TIMESTAMP)",
						"", "id DESC,created DESC", [ Core::$core->url, Core::$core->url, Core::$client->lang ]);
					if(!empty($data['id']))
						Cache::set($C, $data);
					else
						return;
				}
				//! check filters
				if(!empty($data['filter']) && ! Core::cf($data[ 'filter' ])) {
					//! not allowed, fallback to 403
					Core::$core->template = "403";
					return;
				}
				//! set view for page
				Core::$core->template = $data[ 'template' ];
				//! load site title
				Core::$core->title = $data[ 'name' ];
				//! load application property overrides
				$o = json_decode($data[ 'data' ],true);
				if(is_array($o))
					foreach($o as $k=>$v)
						$this->$k=$v;
				foreach([ "id", "name", "lang", "modifyd", "ctrl" ] as $k)
					$this->$k=$data[$k];
				//! get page specific DDS
				$E = json_decode($data['dds'],true);
				if(is_array($E))
					Core::$dds += $E;
			// @codeCoverageIgnoreStart
			} catch(\Exception $e){}
			// @codeCoverageIgnoreEnd
		}

/**
 * Default action
 *
 * @param not used.
 */
		function action($item="")
		{
			//! as this could be considered as a security risk, this feature can be turned off globally
			if(! empty(Core::$core->noctrl) || empty($this->ctrl))
					return;
			ob_start();
			//FIXME: sanitize php code
			eval("namespace PHPPE\Ctrl;\nuse PHPPE\Core as PHPPE;\n" . $this->ctrl);
			return ob_get_clean();
		}

/**
 * Get dynamic data sets into application properties
 *
 * @param application object
 */
		static function getDDS(&$app)
		{
			try{
				//! special page holds global page parameters and dds'
				$F = DS::fetch("data,dds", "pages", "id='frame'","","id DESC,created DESC");
				$E = json_decode($F[ 'data' ],true);
				View::assign("frame", $E);
				//! load global dds
				$E = json_decode($F['dds'],true);
				if(is_array($E)) Core::$dds += $E;
			// @codeCoverageIgnoreStart
			} catch(\Exception $e){}
			// @codeCoverageIgnoreEnd
			$o = [];
			foreach(Core::$dds as $k => $c)
				//! don't allow to set these, as they cannot be arrays
				if(!in_array($k,["dds","id","title","mimetype"]))
				{
					try{
					    $o[ $k ] = @DS::query($c[ 0 ], @$c[ 1 ], strtr(@ $c[ 2 ],["@ID"=>$k]), @$c[ 3 ], @$c[ 4 ], @$c[ 5 ], View::getval(@$c[ 6 ]));
						foreach($o[ $k ] as $i=>$v)
						{
							$d=@json_decode($v['data'],true);
							if(is_array($d)) $o[$k][$i]+=$d;
							unset($o[$k][$i]['data']);
						}
					} catch(\Exception $e) {
						Core::log("W",$k." ".$e->getMessage()." ".implode(" ",$c),"dds");
					}
				}
			//! set application properties
			if(!empty($o))
				foreach($o as $k=>$v)
					$app->$k=$v;
		}

	}

/**
 * View layer
 */
	class View extends Extension
	{
		private static $hdr = [
			"meta"=>[],
			"link"=>[],
			"css"=>[],
			"js"=>[],
			"jslib"=>[]
			];						//!< header items and js libraries
		private static $menu;		//!< system menu, populated by initialized modules
		private static $n;			//!< templater nested level
		private static $c;			//!< templater control structures context
		private static $o = [];		//!< templater objects
		private static $tc;			//!< try button counter
		private static $p;			//!< templater default path for views
		public static $e = "";		//!< last expression to evaluate
/**
 * Initialize event
 * 
 * @param core instance
 */
		static function init($core)
		{
			//! register core, user and client to templater
			self::$o["core"] = $core;
			//! register default meta keywords
			if(!empty(Core::$core->meta) && is_array(Core::$core->meta))
				self::$hdr["meta"]=Core::$core->meta;
			self::$hdr["meta"][ "viewport" ] = "width=device-width,initial-scale=1.0";
			if(!empty(Core::$core->link) && is_array(Core::$core->link))
				self::$hdr["link"]=Core::$core->link;
			self::$tc = 0;
		}

/**
 * Set default path for templater
 *
 * @param path
 */
		static function setPath(&$p)
		{
			self::$p = &$p;
		}

/**
 * Register an object in templater
 *
 * @param name
 * @param instance reference
 */
		static function assign($n, &$o)
		{
			self::$o[ $n ] = &$o;
		}

/**
 * Register a new stylesheet
 *
 * @param name of the stylesheet
 */
		static function css($c = "")
		{
			if(empty($c))
				return self::$hdr["css"];
			//! add a new stylesheet to output
			$a = View::dir(). "/css/" . @explode("?", $c)[ 0 ];
			if(! file_exists($a))
				$a .= ".php";
			if(! isset(self::$hdr["css"][ $c ]) && file_exists($a))
				self::$hdr["css"][ $c ] = realpath($a);
		}

/**
 * Register a new javascript library
 *
 * @param name of the js library
 * @param if it needs to be initialized, the code to do that
 */
		static function jslib($l = "", $i = "")
		{
			if(empty($l))
				return self::$hdr["jslib"];
			//! add a new javascript library to output
			$a = View::dir(). "/js/" . @explode("?", $l)[ 0 ];
			if(! file_exists($a))
				$a .= ".php";
			if(! isset(self::$hdr["jslib"][ $l ]) && file_exists($a))
				self::$hdr["jslib"][ $l ] = realpath($a);
			//! also register init hook and call it on onload event
			$i = trim($i);
			if(! empty($i) && (empty(self::$hdr["js"][ "init()" ]) || strpos(self::$hdr["js"][ "init()" ], $i) === false)) {
				self::js("init()", $i . ($i[ strlen($i) - 1 ] != ";" ? ";" : ""), true);
			}
		}

/**
 * Register a new javascript function
 *
 * @param name of the js function with arguments
 * @param code
 * @param if code should be appended to existing code, true. Replace otherwise
 */
		static function js($f = "", $c = "", $a = 0)
		{
			if($c){
				//! add a javascript function to output
				$C = Assets::minify($c, "js");
				$C .= ($C[ strlen($C) - 1 ] != ";" ? ";" : "");
				if($a) {
					if(! isset(self::$hdr["js"][ $f ]))
						self::$hdr["js"][ $f ] = "";
					if(strpos(self::$hdr["js"][ $f ], $C) === false)
						self::$hdr["js"][ $f ] .= $C;
			}
			else
				self::$hdr["js"][ $f ] = $C;
			}
		}

/**
 * Register a new menu item or submenu in PHPPE panel
 *
 * @param title of the link
 * @param url or array of title=>url
 */
		static function menu($t = "", $l = "")
		{
			if(empty($t))
				return self::$menu;
			//! add a new menuitem or submenu
			if(is_string($l) || is_array($l))
				self::$menu[ $t ] = $l;
		}

/**
 * Load view from cache
 *
 * @param cache key
 * @return cached content or null
 */
		static function fromCache($N)
		{
			$d = Cache::get($N);
			if(is_array($d)) {
				//! cache hit, we are happy!
				foreach([ 'm' => 'meta', 'c' => 'css', 'j' => 'js', 'J' => 'jslib' ] as $k => $v)
					if(is_array($d[ $k ]))
						self::$hdr[$v] = array_merge(self::$hdr[$v], $d[ $k ]);
				return $d[ 'd' ];
			}
			return "";
		}

/**
 * Generate the main part of the view (that is, without html header and footer)
 *
 * @param template to use
 * @param cache key
 * @return generated content
 */
		static function generate($template, $N="")
		{
			//! we should check cache here, but it's already handled
			//! in Core::run() because controller has to be skipped
			$T = self::template($template);
			//! if action specific template not found, fallback to application's
			if(empty($T)) $T = self::template(Core::$core->app."_".Core::$core->action);
			if(empty($T)) $T = self::template(Core::$core->app);
			if(empty($T)) $T = self::template("404");
			//! fallback index page if even 404 template missing
			if(empty($T) && Core::$core->app=="index")
				// @codeCoverageIgnoreStart
				$T="<h1>PHPPE works!</h1>Next step:<pre>php public/".basename(__FILE__)." --diag</pre>";
				// @codeCoverageIgnoreEnd

			if(empty(Core::$core->noframe)) {
				//! replace application marker in frame with output
				$d = self::template("frame");
				//! failsafe frame
				if( !$d ) $T = "<div id='content'>".$T."</div>";
				if(preg_match("/<!app>/ims", $d, $m, PREG_OFFSET_CAPTURE))
					$T = substr($d, 0, $m[ 0 ][ 1 ]) . $T . substr($d, $m[ 0 ][ 1 ] + 6);
			}
			//save to cache
			if(!empty($T) && !empty($N))
				Cache::set($N, [
					"m" => self::$hdr["meta"],
					"l" => self::$hdr["link"],
					"c" => self::$hdr["css"],
					"j" => self::$hdr["js"],
					"J" => self::$hdr["jslib"],
					"d" => $T ]);
			return $T;
		}

/**
 * Load, parse and evaluate a template
 *
 * @param name of the template
 * @return parsed output string
 */
		static function template($n)
		{
			//! set http response header as well for special templates
			if($n == "403" || $n == "404")
				@header("HTTP/1.1 $n " . ($n == "403" ? "Access Denied" : "Not Found"));
			//get template content
			$d = self::get($n);
			if(empty($d))
				return "";
			self::$n = - 1;
			self::$c = [];
			//parse tags
			return self::_t($d);
		}

/**
 * Get value of a templater expression
 *
 * @param expression
 * @return value
 */
		static function getval($x)
		{
			if(!is_string($x))
				return $x;
			//! evaluate an expression for templater and return it's value
			//! security check: look for variables and let operator
			if(strpos($x, "$") !== false || preg_match("/[^!=]=[^=]/", $x))
				return self::e("W", "BADINP", $x);
			$l = $r = "";
			$d = $x;
			//convert expression to php commands
			for($i = 0; $i < strlen($d); $i++ ) {
				$c = $d[ $i ];
				if($c == '"' || $c == "'") {
					$r .= $c;
					$i++ ;
					$b = "";
					while($i < strlen($d) && $b != $c) {
						if($b == "\\")
							$b .= $d[ $i++ ];
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
						$v = substr($d, $i, $j - $i);
						switch($v) {
							case "KEY" :
							case "IDX" :
							case "VALUE" :
								if(isset(self::$c[ self::$n ]->$v))
									$v = self::$c[ self::$n ]->$v;
								if(is_array($v)) return $v;
								//! don't throw notice if non-scalar referenced as VALUE
								@$r .= "'" . addslashes($v) . "'";
								$i = $j;
								break;
							case "ODD" :
								$r .= self::$c[ self::$n ]->IDX % 2;
								$i = $j;
								break;
							case "parent" :
								$Y = self::$n - 1;
								while(substr($d, $j, 7) == ".parent") {
									$j += 7;
									$Y-- ;
								}
								$n = substr($d, $j + 1, 3);
								$j += 4 + ($n == "VAL" ? 2 : 0);
								if($n == "ODD")
									$v = self::$c[ $Y ]->IDX % 2;
								elseif(isset(self::$c[ $Y ]->$n))
									$v = self::$c[ $Y ]->$n;
								elseif(isset(self::$c[ $Y ]->VALUE[ $n ]))
									$v = self::$c[ $Y ]->VALUE[ $n ];
								// just in case, failsafe. Normally never reached
								// @codeCoverageIgnoreStart
								elseif(isset(self::$c[ $Y ]->VALUE->$n))
									$v = self::$c[ $Y ]->VALUE->$n;
								else $v = "";
								// @codeCoverageIgnoreEnd
								$r .= "'" . addslashes($v) . "'";
								$i = $j;
								break;
							case "true" :
							case "false" :
							case "null" :
								break;
							default :
								if(isset(self::$c[ self::$n ]) && is_array(self::$c[ self::$n ]->VALUE)) {
									@ $r .= "'" . addslashes(self::$c[ self::$n ]->VALUE[ $v ]) . "'";
									$i = $j;
								}
								elseif(isset(self::$c[ self::$n ]->VALUE->$v)) {
									@ $r .= "'" . addslashes(self::$c[ self::$n ]->VALUE->$v) . "'";
									$i = $j;
								}
								else
								{
									$r .= "\$";
									$f[ $v ] = 1;
								}
						}
					}
					elseif($b == "(" && ($j - $i != 1 || $d[ $i ] != "L") && substr($d, $i, 5) != "core." &&
						substr($d, $i, $j - $i) != "array" && ! empty(Core::$core->allowed) && ! in_array(substr($d, $i, $j - $i),
						Core::$core->allowed))
						return self::e("E", "BADFNC", $x);
				}
				$r .= ($c == "." ? "->" : (isset($d[ $i ]) ? $d[ $i ] : ""));
				$l = $c;
			}
			//! for string operators
			$r = strtr($r, [ "+'" => ".'", "+\"" => ".\"", "'+" => "'.", "\"+" => "\"." ]);
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
			$e = error_reporting();
			error_reporting($e &~ E_NOTICE);
			ob_start();
			self::$e=$x." => ".$r;
			$R = @eval("return " . $r . ";");
			$o = ob_get_clean();
			error_reporting($e);
			self::$e="";
			if(Core::$core->runlevel > 2 || ! empty($o))
				// @codeCoverageIgnoreStart
				Core::log(! empty($o) ? "E" : "D", $x . " => " . $r . " = " . serialize($R), "view");
				// @codeCoverageIgnoreEnd
			return $R;
		}

/**
 * Return and increase try button counter
 *
 * @param current button
 */
		static function tc()
		{
			return ++self::$tc;
		}


/**
 * Format an error message
 *
 * @param weight (see log())
 * @param module
 * @param message
 * @return formated message
 */
		static function e($w, $c, $m)
		{
			if(!is_string($m))
				$m=json_encode($m);
			return ! empty(Core::$core->output) && Core::$core->output == "html" ?
				"<span style='background:#F00000;color:#FEA0A0;padding:3px;'>" . ($w ? "$w-" : "") . ($c ? "$c:&nbsp;" : "") . htmlspecialchars($m) . "</span>" :
				"$c-$w: " . strtr($m, [ "\r" => "", "\n" => "\\n" ]) . "\n";
		}

/**
 * Evaluate view template. Reentrant
 */
		static function _t($x, $re = 0)
		{
			//! parse a template string
			//check recursion limit
			$L = self::e("W", "TOOMNY", L("recursion limit exceeded")."!");
			if($re >= 64)
				return $L;
			//check if we're in cms edit mode
			$J = /*Core::$core->app == "cms" && Core::$core->action == "pages" &&*/ method_exists("\\PHPPE\\CMS", "icon");
//$J=1;
			//get tags
			if(preg_match_all("/<!([^\[\-][^>]+)>[\r\n]*/ms", $x, $T, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
				//get opening/closing pairs
				$o = [];
				$I = 0;
				foreach($T as $k => $v) {
					$T[ $k ][ 0 ][ 2 ] = strlen($v[ 0 ][ 0 ]);
					$t = strtolower(substr($v[ 1 ][ 0 ], 0, 4));
					if(substr($t, 0, 2) == "if" || $t == "fore" || $t == "temp")
						$o[ $I++ ] = $k;
					elseif($t == "else")
						@$T[ $o[ $I - 1 ] ][ 4 ] = $k;
					elseif(substr($t, 0, 3) == "/if" || $t == "/for" || $t == "/tem")
						@$T[ $o[ -- $I ] ][ 3 ] = $k;
				}
				if($I)
					return self::e("W", "UNCLS", L("unclosed tag"));
				unset($o);
				//parse tags
				$C = 0;
				for($k = 0; $k < count($T) && $m = $T[ $k ]; $k++ ) {
					$H = trim($m[ 1 ][ 0 ]);
					$w = "";
					$a = "";
					if($H[ 0 ] == "=")
						$t = "=";
					else
						$t = trim(strstr($H, ' ', true));
					if(empty($t))
						$t = $H;
					else
						$a = trim(substr($H, strlen($t)));
					$A = Core::x(" ", $a);
					$N = $m[ 0 ][ 1 ];
					$M = $m[ 0 ][ 2 ];
					//interpret tags
					switch($t) {
						//application output marker in frame. It's not our job to parse it
						case "app" :
						$w = "<!app>";
						break;
						//include another template
						case "include" :
						$c = self::get($a);
						if(! $c)
							$c = self::get(self::getval($a));
						$w = self::_t($c, $re + 1);
						break;
						//expression
						case "=" :
						$w = self::getval($a);
						break;
						//re-entrant parsing
						case "template" :
						$w = self::_t(strtr(self::_t(substr($x, $m[ 0 ][ 1 ] + $m[ 0 ][ 2 ] + $C,
							$T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ]), $re), "<%", "<!"), $re + 1);
						$k = $m[ 3 ];
						$M = $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ];
						break;
						//iteration
						case "foreach" :
						$d = self::getval($a);
						self::$n++ ;
						self::$c[ self::$n ] = (object)[ 'IDX' => 1 ];
						$t = substr($x, $m[ 0 ][ 1 ] + $m[ 0 ][ 2 ] + $C, $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ]);
						if((is_array($d) && count($d) > 0) || is_object($d))
							foreach($d as $k => $v) {
								self::$c[ self::$n ]->KEY = $k;
								self::$c[ self::$n ]->VALUE = $v;
								$w .= self::_t($t, $re + 1);
								self::$c[ self::$n ]->IDX++ ;
							}
						$k = $m[ 3 ];
						$M = $T[ $m[ 3 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ];
						unset(self::$c[ self::$n ]);
						self::$n-- ;
						break;
						//conditional
						case "if" :
						$M = $T[ $m[ 3 ] ][ 0 ][ 1 ] + $T[ $m[ 3 ] ][ 0 ][ 2 ] - $m[ 0 ][ 1 ];
						$w = self::_t(($a != "cms" && ! empty(self::getval($a))) || ($a == "cms" && $J) ? substr($x, $N + $C + $m[ 0 ][ 2 ], ! empty($m[ 4 ]) ? $T[ $m[ 4 ] ][ 0 ][ 1 ] - $m[ 0 ][ 1 ] - $m[ 0 ][ 2 ] : $M - $m[ 0 ][ 2 ] - $T[ $m[ 3 ] ][ 0 ][ 2 ]) : (! empty($m[ 4 ]) ? substr($x, $T[ $m[ 4 ] ][ 0 ][ 1 ] + $C + $T[ $m[ 4 ] ][ 0 ][ 2 ], $T[ $m[ 3 ] ][ 0 ][ 1 ] - $T[ $m[ 4 ] ][ 0 ][ 1 ] - $T[ $m[ 4 ] ][ 0 ][ 2 ]) : ""), $re + 1);
						$k = $m[ 3 ];
						break;
						//object reference
						case "form" :
						self::$tc = 0;
						$c = sha1(url());
						$n = ! empty($A[ 0 ]) && $A[ 0 ] != "-" ? urlencode($A[ 0 ]) : "form";
						$w = "<form name='" . $n . "' action='" . url(! empty($A[ 1 ]) && $A[ 1 ] != "-" ? $A[ 1 ] : "") .
							"' method='post' enctype='multipart/form-data'" .
							(! empty($A[ 2 ]) && $A[ 2 ] != "-" ? " onsubmit=\"" . strtr($A[ 2 ], ["\""=>"\\\""]) . "\"" : "") .
							"><input type='hidden' name='MAX_FILE_SIZE' value='" . Core::$fm .
							"'><input type='hidden' name='pe_s' value='" . @$_SESSION[ "pe_s" ][ $c ] .
							"'><input type='hidden' name='pe_f' value='" . $n . "'>" . (! empty(Core::$core->item) ?
								"<input type='hidden' name='item' value='" . htmlspecialchars(Core::$core->item) . "'>" : "");
						break;
						//date and time formating
						case "time" :
						case "date" :
						$v = self::getval($A[ 0 ]);
						$w = ! empty($v) ? date((! empty(Core::$l[ 'dateformat' ]) ?
							Core::$l[ 'dateformat' ] : "Y-m-d") . ($t == "time" ? " H:i:s" : ""), self::ts($v)) : (! empty($A[ 1 ]) ? "" : L("Epoch"));
						break;
						case "difftime" :
						$w = "";
						$v = self::getval($A[ 0 ]);
						if(! empty($A[ 1 ])) {
							if(! $v) {
								$w = "-";
								break;
							}
							$v -= self::ts(self::getval($A[ 1 ]));
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
						case "L" :
						$w = L($a);
						break;
						//dump object - this only works if runlevel is at least testing (1)
						case "dump" :
						$l = Core::$core->runlevel;
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
								$n = ob_get_clean();
								if($n[ 0 ] != "<")
									$n = "<pre>" . $n . "</pre>";
							}
							else
							{
								print_r($s);
								$n = "<pre>" . htmlspecialchars(ob_get_clean()) . "</pre>";
							}
							$w = "<b style='font:monospace;'>" . $A[ 0 ] . ":</b>" . preg_replace("/<small>.*?<\/small>\n?/","",$n);
						}
						break;
						//hook for cms editor icons
						case "cms" :
						//add-on support
						case "widget" :
						case "var" :
						case "field" :
						$Z = $R = $m = false; $G=$t=="cms"; $V=$t=="var";
						//if first attribute starts with an at sign, it's an ace definition
						if($A[ 0 ][ 0 ] == '@') {
							$Z = substr($A[ 0 ], 1);
							array_shift($A);
						}
						$Z=empty($Z) || Core::$user->has($Z);
						if($G&&$J&&$Z) $w = CMS::icon($A);
						//if type starts with an asterix, it's a mandatory field
						//equal sign does not show error on missing addon, but display plain value
						if($A[ 0 ][ 0 ] == '*') {
							$R = true;
							$A[ 0 ] = substr($A[ 0 ], 1);
						}
						$f = $A[ 0 ];
						//get add-on type and arguments
						if(preg_match("/^([^\(]+)[\(]?([^\)]*)/", $A[ 0 ], $B) && ! empty($B[ 1 ])) {
							//submit is just an alias of update
							$f = $B[ 1 ] == "submit" ? "update" : $B[1];
							//get arguments array
							$a = self::getval("[" . $B[ 2 ] . "]");
							array_shift($A);
							//name
							$n = ! empty($A[ 0 ]) ? $A[ 0 ] : "";
							array_shift($A);
							//value (if applicable)
							if(! in_array($f, [ "update", "cancel", "button" ]))
								$v = self::getval($n);
							//find appropriate class for AddOn
							$d = "\\PHPPE\\Addon\\".$f;
							if(! class_exists($d) && $D = @glob("vendor/phppe/*/addons/" . $f . ".php")[ 0 ])
								include_once($D);
							if(class_exists($d) && is_subclass_of($d, "\\PHPPE\\AddOn")) {
								//ok, got it
								$F = new $d($a, $n, $v, $A, $R);
								//if it has an init() method, and not called yet, call it
								if(empty(Core::$core->addons[ $f ]))
									if(method_exists($F, "init"))
										$F->init();
									else
										Core::addon($f, "addon $f");
								//add validators
								if($R || $f == "check" || $f == "file" || method_exists("\\PHPPE\\Addon\\".$f, "validate"))
									$_SESSION[ "pe_v" ][ $n ][ $f ] = [ $R, $a, $A ];
								//find out method to use to draw AddOn
								$m = !$G && ($t == "field" || ! empty($_SESSION[ $V ? "pe_e" : "pe_c" ])) && method_exists($F, "edit") ? "edit" : "show";
								//get output
								$w .= method_exists($F, $m) && $Z ? $F->$m() : $v;
								unset($F);
								break;
							}
						}
						$w .= /*$G ||*/ ($V && empty($_SESSION[ 'pe_e' ])) ? (is_scalar($v) ? $v . "" : $v && json_encode($v)) : self::e("W", "UNKADDON", $f);
						break;
						default : $w = self::e("W", "UNKTAG", $t);
					}
					//replace templater tag with output, not using any search-and-replace algorithms
					$D = $N + $C && $x[ $N + $C - 1 ] == "\n" ? 1 : 0;
					$E = isset($x[ $N + $M + $C ]) && $x[ $N + $M + $C ] == "\n" ? 1 : 0;
					$x = substr($x, 0, $N + $C - $D) . $w . substr($x, $N + $M + $C + $E);
					$C += @strlen($w) - $M - $D - $E;
				}
			}
			return $x;
		}

/**
 * Generate and flush output to client
 *
 * @param pre-generated main part
 */
		static function output(&$txt)
		{
			//! get output format
			$o = Core::$core->output;
			//! application may override mime type of output
			Http::mime((! empty(self::$o["app"]->mimetype) ? self::$o["app"]->mimetype : "text/" . ($o ? $o : "html")), false);
			//! output header
			if($o) {
				//! look for extension
				$c = @glob("vendor/phppe/*/out/" . $o . "_header.php");
				if(! empty($c[ 0 ])) include_once($c[ 0 ]);
				//! if not found, fallback to built-in version for html
				elseif($o == "html") {
					$P = empty(Core::$core->nopanel) && Core::$user->has("panel");
					$I = basename(__FILE__)."/";
					if($I == "index.php/") $I = "";
					$d = "http" . (Core::$core->sec ? "s" : "") . "://" . Core::$core->base;
					//! HTML5 header and title
					echo("<!DOCTYPE HTML>\n<html lang='".Core::$client->lang."'".(!empty(Core::$l['rtl'])?" dir='rtl'":"").">".
					"<head><title>" . (! empty(self::$o["app"]->title)?self::$o["app"]->title:(
					Core::$core->title ? Core::$core->title : "PHPPE".VERSION)) . "</title>".
					"<base href='$d'/><meta charset='utf-8'/>\n<meta name='Generator' content='PHPPE".VERSION."'/>\n");
					//! meta tags
					foreach(array_merge(self::$hdr["meta"], !empty(self::$o["app"]->meta) ? self::$o["app"]->meta : [ ]) as $k => $m)
						if($k && $m)
							echo("<meta name='$k' content='" . htmlspecialchars($m) . "'/>\n");
					//! favicon
					if(!empty(self::$o["app"]->favicon))
						self::$hdr["link"]["shortcut icon"]=self::$o["app"]->favicon;
					if(empty(self::$hdr["link"]["shortcut icon"]))
						self::$hdr["link"]["shortcut icon"]="favicon.ico";
					//! link tags
					foreach(self::$hdr["link"] as $k => $m)
						if($m)
							echo("<link rel='$k' href='" . htmlspecialchars($m) . "'/>\n");
					//! add style sheets (async)
					$O = "<style media='all'>\n";
					$d = "@import url('%s');\n";
					$N = Core::$core->base.Core::$core->url."/" . Core::$user->id . "/".Core::$client->lang;
					$e="css";
					//! admin css if user logged in and has access
					if($P)
						$O .= sprintf($d, $I."?cache=$e");
					//! user stylesheets
					if(! empty(self::$hdr["css"])) {
						//! if aggregation allowed
						if(! empty(Cache::$mc) && empty(Core::$core->noaggr)) {
							$n = sha1($N . "_$e");
							if(empty(Cache::get("c_$n"))) {
								$da = "";
								//! skip dynamic assets (they use a different caching mechanism)
								foreach(self::$hdr["css"] as $u => $v)
									if($v && substr($v, - 3) != "php" && $u[ 0 ] != "?")
										$da .= Assets::minify(file_get_contents($v), $e) . "\n";
								//! save result to cache
								Cache::set("c_$n", [ "m" => "text/$e", "d" => $da ]);
							}
							$O .= sprintf($d, $I."?cache=$n");
							//! add dynamic stylesheets, they were left out from aggregated cache above
							foreach(self::$hdr["css"] as $u => $v)
								if($v && ($u[ 0 ] == "?" || substr($v, - 3) == "php"))
									$O .= sprintf($d, ($u[ 0 ] == "?" ? "" : $I."$e/") . $u);
						}
						else
						{
							foreach(self::$hdr["css"] as $u => $v)
								if($v)
									$O .= sprintf($d, ($u[ 0 ] == "?" ? "" : $I."$e/") . $u);
						}
					}
					$O .= "</style>\n";
					echo("$O</head>\n<body" . (! empty(self::$hdr["js"][ "init()" ]) ? " onload='init();'" : "") . ">\n");
					//! display PHPPE panel
					if($P) {
						$H = " class='sub' style='visibility:hidden;' onmousemove='return pe_w();'";
						$O = "<div id='pe_p'><a href='" . url("/") . "'><img src='$I?cache=logo' alt='PHPPE" . VERSION . "' style='margin:3px 10px -3px 10px;float:left;'></a><div class='menu'>";
						//! menu items and submenus
						$x = 0;
						if(! empty(self::$menu)) {
							foreach(self::$menu as $e => $L) {
								//! access check
								@list($ti, $a) = explode("@", $e);
								if($a && ! Core::$user->has($a)) continue;
								$a = 0;
								if(is_array($L))
									$l = $L[ array_keys($L)[ 0 ] ];
								else
									$l = $L;
								$U = Core::$core->url;
								if(substr($l, 0, strlen($U)) == $U)
									$a = 1;
								else
								{
									$d = explode("/", $l);
									if(Core::$core->app == (! empty($d[ 0 ]) ? $d[ 0 ] : "index"))
										$a = 1;
									unset($d);
								}
								if(is_array($L)) {
									$O .= "<div id='pe_m$x'$H><ul>";
									foreach($L as $t => $l)
										if($t)
										{
											@list($Y, $A) = explode("@", $t);
											if(empty($A) || Core::$user->has($A))
												$O .= "<li onclick=\"document.location.href='" . $I . "$l';\"><a href='" . $I . "$l'>" . htmlspecialchars(L($Y)) . "</a></li>";
										}
									$O .= "</ul></div><span class='menu_" . ($a ? "a" : "i") . "' onclick='return pe_p(\"pe_m$x\");'>" . htmlspecialchars(L($ti)) . "</span>";
									$x++ ;
								}
								else
									$O .= "<span class='menu_" . ($a ? "a" : "i") . "'><a href='" . $I . "$L'>" . htmlspecialchars(L($ti)) . "</a></span>";
							}
						}
						//! call extensions status hooks
						$O .= "</div><div class='stat'>";
						//! *** STAT Event ***
						foreach(Core::lib() as $d)
							if(method_exists($d, "stat"))
								$O .= "<span>" . $d->stat() . "</span>";
						//! language selector box
						$O .= "<div id='pe_l'$H><ul>";
						if(! empty($_SESSION[ 'pe_ls' ])) $d = $_SESSION[ 'pe_ls' ];
						else
						{
							//if application has translations, use that list
							//if not, fallback to core's translations
							$D = @scandir("app/lang");
							if(!is_array($D)) $D=[];
							$d = @scandir("vendor/phppe/Core/lang");
							if(is_array($d)) $D=array_unique($D+$d);
							$d = [];
							foreach($D as $f)
								if(substr($f, - 4) == ".php")
									$d[ substr($f, 0, strlen($f) - 4) ] = 1;
							$_SESSION[ 'pe_ls' ] = $d;
						}
						foreach($d as $k => $v)
							if($k)
								$O .= "<li><a href='" . url() . "?lang=$k'><img src='images/lang_$k.png' alt='$k' title='$k'>" . ($k != L($k) ? "&nbsp;" . L($k) : "") . "</a></li>";
						$O .= "</ul></div>";
						//! current language and user menu
						$k = Core::$client->lang;
						$f = "images/lang_$k.png";
						$c = ! empty($_SESSION[ 'pe_c' ]);
						$O .= "<span onclick='return pe_p(\"pe_l\");'>" .
							(file_exists("vendor/phppe/Core/".$f) ? "<img src='$f' height='10' alt='$k' title='$k'>" : $k) . "</span>".
							"<div id='pe_u'$H><ul><li onclick='pe_p(\"\");if(typeof users_profile==\"function\")users_profile(this);else alert(\"" . L("Install PHPPE Users") . "\");'>" . L("Profile") . "</li>" .
							(Core::$user->has("conf") ? "<li><a href='" . url() . "?conf=" . (1 - $c) . "'>" . L(($c ? "Lock" : "Unlock")) . "</a></li>" : "") .
							"<li><a href='" . url("logout") . "'>" . L("Logout") . "</a></li></ul></div>".
							"<span onclick='return pe_p(\"pe_u\");'>" . (! empty(Core::$user->name) ? Core::$user->name : "#" . Core::$user->id) . "</span></div></div>".
							"<div style='height:32px !important;'></div>\n";
						echo $O;
					}
				}
			}
			//! output main content (generated earlier)
			echo($txt);
			//! output footer
			if($o) {
				//! look for extension
				$c = @glob("vendor/phppe/*/out/" . $o . "_footer.php");
				if(! empty($c[ 0 ])) include_once($c[ 0 ]);
				//! fallback to built-in version for html
				elseif($o == "html") {
					//! add javascript libraries (async)
					$d = "<script";
					$e = "</script>\n";
					$a = " src='".$I."js/";
					$O = "";
					if(! empty(self::$hdr["jslib"])) {
						//! if aggregation allowed
						if(! empty(Cache::$mc) && empty(Core::$core->noaggr)) {
							$n = sha1($N . "_js");
							if(empty(Cache::get("c_$n"))) {
								$da = "";
								//! skip dynamic assets (they use a different caching mechanism)
								foreach(self::$hdr["jslib"] as $u => $v)
									if($v && substr($v, - 3) != "php")
										$da .= Assets::minify(file_get_contents($v), "js") . "\n";
								Cache::set("c_$n", [ "m" => "text/javascript", "d" => $da ]);
							}
							$O .= "$d src='${I}js/?cache=$n'>$e";
							//! add dynamic javascripts, they were left out from aggregated cache above
							foreach(self::$hdr["jslib"] as $u => $v)
								if($v && ($u[ 0 ] == "?" || substr($v, - 3) == "php"))
									$O .= "$d$a$u'>$e";
						}
						else
						{
							foreach(self::$hdr["jslib"] as $u => $v)
								if($v)
									$O .= "$d$a$u'>$e";
						}
					}
					//load PHPPE\Users' JS library if it's not aggregated already and PHPPE panel is shown
					$c = "users.js";
					$i=file_exists("vendor/phppe/Users/js/" . $c . ".php");
					if($P && ! isset(self::$hdr["jslib"][ $c ]) && $i) $O .= "$d$a$c'>$e";
					//! add javascript functions
					$c = self::$hdr["js"];
					$a = "";
					//! built-in stuff if core.js is not installed
					if($P && !$i) {
						$x = "document.getElementById(";
						$y = ".style.visibility";
						$a = "pe_t=setTimeout(function(){pe_p('');},2000)";
						$c[ "L(t)" ] = "return t.replace(/_/g,' ');";
						$c[ 'pe_p(i)' ] = "var o=i?${x}i):i;if(pe_t!=null)clearTimeout(pe_t);if(pe_c&&pe_c!=i)${x}pe_c)$y='hidden';pe_t=pe_c=null;if(o!=null&&o.style!=null){if(o$y=='visible')o$y='hidden';else{o$y='visible';pe_c=i;$a;}}return false;";
						$c[ 'pe_w()' ] = "if(pe_t!=null)clearTimeout(pe_t);$a;return false;";
						$a = ",pe_t,pe_c";
					}
					if(! empty($c)) {
						$O .= $d . ">\nvar pe_ot=" . ($P ? 31 : 0) . "$a;\n";
						foreach($c as $fu => $co)
							$O .= "function $fu {" . $co . "}\n";
						$O .= $e;
					}
					$D = Core::isError();
					$s = Core::started();
					$d = 'REQUEST_TIME_FLOAT';
					$T = ! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : $s;
					echo("\n$O<!-- MONITORING: " . ($D > 0 ? "ERROR" : (Core::$core->runlevel > 0 ? "WARNING" : "OK")) .
					", page " . sprintf("%.4f sec, db %.4f sec, server %.4f sec, mem %.4f mb%s -->\n</body>\n</html>\n",
					microtime(1) - $T,
					DS::bill(), $s - $T, memory_get_peak_usage() / 1024 / 1024, ! empty(Cache::$mc) && empty(Core::$core->nocache) ? ", mc" : ""));
				}
			}
			flush();
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
			$d = @file_get_contents($o);
			if(! function_exists("gd_info") || empty($d) || ! ($i = @imagecreatefromstring($d))) {
				Core::log('W', "no php-libgd or bad image: $o", "picture");
				if(file_exists($o))
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
			if(! empty($W)) {
				$g = @imagecreatefrompng($W);
				if(!empty($g)) {
					$a = imagesx($g);
					$b = imagesy($g);
					for($y = 0; $y < $Y; $y += $a)
						for($x = 0; $x < $X; $x += $a)
						imagecopyresampled($N, $g, $x, $y, 0, 0, $a, $b, $a, $b);
				} else
					Core::log('W', "bad watermark image: $W", "picture");
			}
			//! reduce quality to match file maximum byte size requirement
			@unlink($n);
			while(! file_exists($n) || (filesize($n) > $s && $q >= $m)) {
				@unlink($n);
				if(! $j($N, $n, $q-- )) {
					// @codeCoverageIgnoreStart
					@copy($o, $n);
					return false;
					// @codeCoverageIgnoreEnd
				}
			}
			return true;
		}

/**
 * Helper function to generate html for built-in fields
 */
		static function v($a, $b, $e = "", $f = [], $p = "")
		{
			return (@$a->css[0]=="r"?" required":"").
				($p?" pattern='".$p."'":"").
				" class='" . $a->css .(! empty($b) && $b != "-" ? " " . $b : "") . "'" .
				($a->name ? " name='" . $a->fld . "'" : "") .
				($e && $e != "-" ? " onchange='" . $e . "'" : "") .
				(! empty($f[ 0 ]) && $f[ 0 ] > 0 ? " maxlength='" . $f[ 0 ] . "'" : "");
		}

/**
 * Load a raw template
 *
 * @param name of the template
 * @return template string, cached if available
 */
		static function get($n)
		{
			//! get a template in raw format
			$t = "";
			$m = [];
			$V = "views";
			$e = ".tpl";
			//! from cache if possible
			$C = 't_' . sha1(Core::$core->base . "_" . $n);
			if($p = Cache::get($C) && !empty($p) && is_array($p) && ! empty($p[ 'd' ])) $t = $p[ 'd' ];
			//! on cache miss
			if(empty($t)) {
				//! from database
				if(! empty(DS::db())/* && file_exists("vendor/phppe/Core/sql/$V.sql")*/) {
					try {
						foreach([ Core::$core->app . "/" . $n, $n ] as $v) {
							$p = DS::fetch("*", $V, "id=?", "", "", [ $v ]);
							if(! empty($p[ 'data' ])) {
								foreach([ "css", "jslib" ] as $c) {
									$t = json_decode($p[ $c ],true);
									if(is_array($t))
										foreach($t as $v)
											self::$hdr[$c][ basename($v) ] = $v;
								}
								$t = $p[ 'data' ];
								break;
							}
						}
					// @codeCoverageIgnoreStart
					} catch(\Exception $e) {}
					// @codeCoverageIgnoreEnd
				}
				//! from file - fallback if not found in database
				if(! $t)
					foreach([ "app/$V/$n$e",
						self::$p ? self::$p . "/$V/$n$e" : "",
						"vendor/phppe/". Core::$core->app . "/$V/$n$e",
						"vendor/phppe/Core/$V/$n$e" ] as $F)
						if($F && file_exists($F))
						{
							$t = file_get_contents($F);
							break;
						}
				//! failsafe: remove comments and php tags
				$t = preg_replace("/<!-.*?->[\r\n]*/ms", "", preg_replace("/<\?.*?\?\>[\r\n]*/ms", "", $t));
				//! save to cache
				if(!empty($t))
				{
					Cache::set($C, [ "d" => $t ]);
				}
			}
			//! return raw template
			return $t;
		}

/**
 * Return the base directory for the extension calling
 *
 * @return path
 */
		private static function dir()
		{
			$d=dirname(debug_backtrace()[ 1 ][ 'file' ]);
			if(in_array(basename($d), [ "ctrl", "libs", "js", "css", "addons", "tests" ]))
				$d = dirname($d);
			return $d;
		}

/**
 * Return a timestamp
 *
 * @param timestamp or date
 * @return timestamp
 */
		private static function ts($v)
		{
			return preg_match("|^[0-9]+$|", $v) ? $v : strtotime($v);
		}

// @codeCoverageIgnoreStart
/**
 * Dump view objects
 */
		static function dump()
		{
//			Http::mime("text/plain", false);
			echo("<pre>");
			print_r($_SERVER);
			print_r(self::$o);
			echo("</pre>");
			phpinfo();
			die;
		}
// @codeCoverageIgnoreEnd

	}

// @codeCoverageIgnoreStart
/**
 * Some useful tool for file manipulations
 */
	class Tools extends Extension
	{
		//! recursive directory delete
		static function rmdir($dir) {
			if(is_dir($dir)){
				$d=glob($dir."/*");
				foreach($d as $v)
					self::rmdir($v);
				rmdir($dir);
			} else
				unlink($dir);
		}

		//! archive extractor (file can be pkzip,gz,bz2,tar,cpio,pax)
		static function untar($file,$fn=""){
			//! detect format
			$body="";$f=gzopen($file,"rb");if($f){$read="gzread";$close="gzclose";$close="gzclose";$open="gzopen";}else{$f=bzopen($file,"rb");if($f){$read="bzread";$close="bzclose";$close="bzclose";$open="bzopen";}else throw new \Exception(L("Unable to open ").": ".$file);}
			//! read archive
			$data=$read($f,512);$close($f);if($data[0]=='P'&&$data[1]=='K') {$zip=zip_open($file);if(!$zip) throw new \Exception(L("Unable to open ").": ".$file);while($zip_entry=zip_read($zip)) {$zname=zip_entry_name($zip_entry);if(!zip_entry_open($zip,$zip_entry,"r")) continue;$zip_fs=zip_entry_filesize($zip_entry);if(empty($zip_fs)) continue;$body=zip_entry_read($zip_entry,$zip_fs);if(!empty($fn) && is_string($fn)) { zip_entry_close($zip_entry); zip_close($zip); return $body; }if(is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$zname,$body);zip_entry_close($zip_entry);}zip_close($zip);return;}
			$f=$open($file,"rb");$ustar=substr($data,257,5)=="ustar"?1:0;while(!feof($f)&&$data){$name="";if($ustar){$data=$read($f,512);$size=octdec(substr($data,124,12));$body=$size>0?$read($f,floor(($size+511)/512)*512):"";$i=0;while(isset($data[$i])&&ord($data[$i])!=0&&$i<512)$i++;$name=substr($data,0,$i);} else{$data=$read($f,110);if(substr($data,0,6)!="070701") throw new \Exception(L("Bad format"));$size=floor((hexdec(substr($data,54,8))+3)/4)*4;$len=hexdec(substr($data,94,8));$len+=floor((110+$len+3)/4)*4-110-$len;$name=trim($read($f,$len));$body="";if($name=="TRAILER!!!") break;$body=$read($f,$size);}if(empty($name)) {$close($f);return "";}
			//! if argument was a filename, return it's contents
			if(!empty($fn) && is_string($fn) && $name==$fn) {$close($f);return substr($body,0,$size);}
			//! if argument was an array with class and method name, call it on every file in the archive
			if($size>0 && is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$name,substr($body,0,$size));
			}$close($f);
		}

		//! copy files to a remote server over a secure channel
		static function copy($files,$dest="")
		{
			// @codeCoverageIgnoreStart
			//! check for remote configuration
			if( empty(Core::$user->data['remote']['identity']) || empty(Core::$user->data['remote']['user']) || empty(Core::$user->data['remote']['host']) || empty(Core::$user->data['remote']['path']) )
				throw new \Exception("PHPPE-E: ".L("configure remote access"));

			//! we cannot install localy, that would use webserver's user, forbidden to write.
			//! So we must use remote user identity even when host is localhost.
			ob_start();
			$idfile = tempnam(".tmp", ".id_");
			file_put_contents($idfile, trim(Core::$user->data['remote']['identity'])."\n");
			chmod($idfile,0400);
			if(is_string($files)) $files=[$files];
			foreach($files as $k=>$v) $files[$k]=escapeshellarg($v);
			$cmd="tar -cz ".implode(" ",$files)."|ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(Core::$user->data['remote']['user']).
				(!empty(Core::$user->data['remote']['port'])&&Core::$user->data['remote']['port']>0?" -p ".intval(Core::$user->data['remote']['port']):"").
				" ".escapeshellarg(Core::$user->data['remote']['host']).
				" sh -c \\\" tar -xvz ".($dest?" -C ".escapeshellarg(Core::$user->data['remote']['path']."/".$dest):"")." 2>\&1 \\\" 2>&1";
			passthru($cmd);
			$r=trim(ob_get_clean());
			unlink($idfile);
			if( in_array(substr($r,0,4),["ssh:","tar:"])||substr($r,0,3)=="sh:" )
				throw new \Exception("PHPPE-E: ".sprintf(L("failed to copy %d files to %s"),count($files),Core::$user->data['remote']['user']."@".Core::$user->data['remote']['host'].":".Core::$user->data['remote']['path']."/".$dest)
					.": ".explode("\n",$r)[0]);
			return $r;
		}
	}
// @codeCoverageIgnoreEnd

/****** PHPPE Core ******/
/**
 * this is the heart of PHPPE, the class of \PHPPE\Core::$core
 */
	class Core
	{
		//generated properties
		private $id;				//!< magic, 'PHPPE'+VERSION
		public $base;				//!< base url
		public $url;				//!< whole url after script name
		public $app;				//!< main page generator controller
		public $action;				//!< main subpage generator action (extends page)
		public $item;				//!< item to work with, usually an id
		public $template;			//!< templater app's template to use
		public $now;				//!< current server timestamp, from primary datasource if available
		//configurable properties
		public $title;				//!< title of the site
		public $runlevel = 2;		//!< 0-production,1-test,2-development,3-debug
		public $syslog = false;		//!< send logs to syslog
		public $trace = false;		//!< save trace to log messages
		public $timeout;			//!< session timeout
		public $mailer;				//!< mailer backend (smtp relay url)
		public $nocache = false;	//!< skip cache
		public $cache;				//!< memcache url
		public $cachettl = 600;		//!< whole output cache ttl in sec
		public $db = "";			//!< primary datasource
		public $noctrl = false;		//!< do not execute Content Controller code
		public $output;				//!< templater output header and footer selector
		//end of configurable properties
		public $form;				//!< name of the submitted form
		public static $core;		//!< self reference, phppe system
		public static $user;		//!< user layer
		public static $client;		//!< client data
		public static $l = [];		//!< language translations
		public static $fm;			//!< file max size
		public static $dds = [];	//!< dynamic data sets
		private $try;				//!< none-zero for update transaction starts, 1 up to 9
		private $error;				//!< error messages array
		private $libs = [];			//!< list of initialized modules and libraries
		private $addons = [];		//!< list of initialized widgets
		private $disabled = [];		//!< list of disabled extensions
		private static $started;	//!< script start time in msec, float
		private static $v;			//!< validator data
		static $paths;				//!< direcories of extensions
		static $w;					//!< boolean, true if called via web (REQUEST_METHOD not empty)
		static $g;					//!< posix group

/**
 * Constructor. If you pass true as argument, it will build up PHPPE environment,
 * but won't run your application. For that you'll need to call \PHPPE\Core::$core->run()
 *  step 1: check and patch php
 *  step 2: self check
 *  step 3: load framework configuration
 *  step 4: autoload classes
 *  step 5: determine bootstrap type
 *
 * @param true is called as a library
 * @return \PHPPE\Core instance
 */
		function __construct($islib = true)
		{
			//! server time is calculated with (this - http request arrive time)
			self::$started = microtime(1);
			//! set self reference for singleton
			self::$core = $core = &$this;
			//! patch php, set defaults

			set_exception_handler(function($e)
			{
				self::log('C', "Exception ".$e->getFile()."(".$e->getLine()."): " . $e->getMessage().(\PHPPE\View::$e?"\n".\PHPPE\View::$e:"").(empty(Core::$core->trace)?"":"\n\t".strtr($e->getTraceAsString(),["\n"=>"\n\t"])),$e->getTrace()[0]['function']=="getval"?"view":"");
			});

			ini_set("error_log", dirname(__DIR__) . "/phppe/log/php.log");
			ini_set("log_errors", 1);
			//! php version check
			if(version_compare(PHP_VERSION, "7.0") < 0)
				// @codeCoverageIgnoreStart
				self::log("C", "PHP 7.0.0 required, found " . PHP_VERSION);
				// @codeCoverageIgnoreEnd
			ini_set("file_uploads", 1);
			ini_set("upload_tmp_dir", dirname(__DIR__) . "/.tmp");
			ini_set("uploadprogress.file.filename_template", dirname(__DIR__) . "/.tmp/upd_%s.txt");
			mb_internal_encoding("utf-8");
			//! self check
			//! this will be updated by the Developer extension's
			//! Repository::compress() when called with mkrepo or deploy
			//$c=__FILE__;if(filesize($c)!=99999||'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'!=sha1(preg_replace("/\'([^\']+)\'\!\=sha1/","''!=sha1",file_get_contents($c))))self::log("C","Corrupted ".basename($c));
			//!
			//! set default working directory to ProjectRoot
			chdir(dirname(__DIR__));
			//! initialize PHPPE environment
			//! load framework configuration
			$c = "vendor/phppe/Core/config.php";
			// @codeCoverageIgnoreStart
			if(file_exists($c))
			{
				$cfg=@require_once($c);
				if(is_array($cfg))
					foreach($cfg as $k=>$v)
						$this->$k=$v;
			} else
				$this->syslog=true;
			//! range checks
			$this->id = "PHPPE" . VERSION;
			if($this->runlevel < 0 || $this->runlevel > 3)
				$this->runlevel = 0;
			if($this->timeout < 60)
				$this->timeout = 7 * 24 * 3600;
			if($this->cachettl < 10)
				$this->cachettl = 10;
			//! functions allowed in view expressions
			if(! empty($this->allowed) && ! is_array($this->allowed))
				$this->allowed = explode(",", $this->allowed);
			//! disabled extensions
			if(! is_array($this->disabled))
				$this->disabled = explode(",", $this->disabled);
			// @codeCoverageIgnoreEnd
			//! patch php. these have to be done *after* config loaded
			ini_set("display_errors", $this->runlevel > 1 ? 1 : 0);
			$this->now = time();
			$this->error = [];
			$this->sec = strtolower(getenv("HTTPS")) == "on" ? 1 : 0;
			//! set up some default values
			self::$w = isset($_SERVER[ 'REQUEST_METHOD' ]) ? 1 : 0;
			$this->output = self::$w ? "html" : "ncurses";
			$this->try = 0;
			//! calculate upload max file size
			$c = self::toBytes("post_max_size");
			$d = self::toBytes("upload_max_filesize");
			$v = self::toBytes("memory_limit");
			if($c > $d && $d)
				$c = $d;
			self::$fm = ($c > $v && $v ? $v : $c);
			//! construct base href
			$c = $_SERVER[ 'SCRIPT_NAME' ];
			$C = dirname($c);
			if($C == ".") $C = "";
			elseif($C != "/") $C .= "/";
			$d = "SERVER_NAME";
			$this->base = (! empty($this->base) ? $this->base : 
				(! empty($_SERVER[ $d ]) ? $_SERVER[ $d ] : "localhost") .
				(@$C[ 0 ] != "/" ? "/" : "") . $C);
			//! fix slashes in request
			if(get_magic_quotes_gpc()) {
				// @codeCoverageIgnoreStart
				foreach($_REQUEST as $k => $v)
					if(is_string($v))
						$_REQUEST[ $k ] = stripslashes($v);
				elseif(a($v))
					foreach($v as $K => $V)
						if(is_string($V))
							$_REQUEST[ $k ][ $K ] = stripslashes($V);
				// @codeCoverageIgnoreEnd
			}
			//! get current requested url
			list($d) = explode("?", @ $_SERVER[ 'REQUEST_URI' ]);
			foreach([ $c, dirname($c) ] as $C)
				if($C != "/" && substr($d, 0, strlen($C)) == $C) { $d = substr($d, strlen($C)); break; }
			if(@$d[ 0 ] == "/") $d = substr($d, 1);
			$D = explode("/", ! empty($d) ? "/".$d : "//");
			//! get current application, action and item.
			//! these can be overriden by url route as well as route events
			foreach([ 1=>"app", 2=>"action", 3=>"item" ] as $c => $v)
				$this->$v = ! empty($D[ $c ]) ? $D[ $c ] : 
					(! empty($_REQUEST[ $v ]) ? trim($_REQUEST[ $v ]) : 
					(! self::$w && ! empty($_SERVER[ "argv" ][ $c ]) && 
					$_SERVER[ "argv" ][ $c ] != "--dump" ? trim($_SERVER[ "argv" ][ $c ]) :
					 ($c < 3 ? ($c == 1 ? "index" : "action") : "")));
			if(empty($d))
				$d=$this->app."/".$this->action.(!empty($this->item)?"/".$this->item:"");
			$this->url = $d;
			//! check arguments
			if(! self::$w && !$islib) {
				// @codeCoverageIgnoreStart
				if(in_array("--version", $_SERVER[ "argv" ]))
					die(VERSION . "\n");
				if(empty($_SERVER[ "argv" ][ 1 ]) || in_array("--help", $_SERVER[ "argv" ])){
					$c="php " . $_SERVER[ "argv" ][ 0 ];
					die("PHP Portal Engine " . VERSION . ", LGPL 2016 bzt\n\n $c --help\n $c --version\n $c --diag [--gid=x]\n $c [application [action [item]]] [--dump]\n\n");
				}
				// @codeCoverageIgnoreEnd
			}

			//! session restore may require models, so we have to
			//! load all classes *before* session_start()

			//! PHP Composer / PHPPE ClassMap autoload support (if exists)
			@include_once("vendor/autoload.php");
			//! register built-in modules
			$this->libs["DS"]=new DS($this->db);
			$this->libs["Client"]=new Client;
			$cls = "\\PHPPE\\User";
			//! this code is tricky. Core defines PHPPE\User, while Pack ships PHPPE\Users.
			//! we'll use the later if found, and fallback to the former.
			@include_once("vendor/phppe/Core/libs/Users.php");
			if(class_exists($cls."s")) $cls .= "s";
			$this->libs["Users"]=new $cls;
			$this->libs["Cache"]=new Cache($this->cache);
			$this->libs["Assets"]=new Assets;
			$this->libs["Tools"]=new Tools;
			//! autoload extensions
			$d = @glob("vendor/phppe/*", GLOB_NOSORT | GLOB_ONLYDIR);
			foreach($d as $f) 
			{
				//! save extension path
				$c=basename($f);
				self::$paths[strtolower($c)]=$f;
				//! look for init code. This file should
				//!  1. load required classes under libs/ if any
				//!  2. set routes if any
				//!  3. return a service instance if any
				//! an empty init.php will also load the extension
				if(!in_array($c, $this->disabled) &&
					file_exists($f."/init.php"))
				{
					$cls="\\PHPPE\\".$c;
					$o = include_once($f."/init.php");
					if(!is_object($o) && class_exists($cls) && $c!="Core") $o = new $cls;
					if(empty($this->libs[ $c ]))
						$this->libs[ $c ] = is_object($o) ? $o : new Extension;
				}
			}
			//! start user session
			session_name(! empty($this->sessionvar) ? $this->sessionvar : "pe_sid");
			session_start();
			//! refresh session cookie
			if(ini_get("session.use_cookies"))
				setcookie(session_name(), session_id(), $this->now + $this->timeout,
				 "/", $this->base, ! empty($this->secsession) ? 1 : 0, 1);
			//! destroy user session if requested
			if(isset($_REQUEST[ 'clear' ])) {
				// @codeCoverageIgnoreStart
				//! save logged in user if any
				$d='pe_u';
				$u = @$_SESSION[ $d ];
				$_SESSION = [];
				$_SESSION[ $d ] = $u;
				//! redirect user to reload everything
				Http::redirect();
				// @codeCoverageIgnoreEnd
			}

			//! detect bootstrap type
			if(!self::$w && !$islib && @$_SERVER[ "argv" ][ 1 ] == "--diag") $this->bootdiag();
			else
			{
				//! normal bootsrap
				//! Cache hit, not in debug and developer mode
				$d = 'HTTP_IF_MODIFIED_SINCE';
				// @codeCoverageIgnoreStart
				if($this->runlevel<2 && isset($_SERVER[ $d ]) && strtotime($_SERVER[ $d ]) + $this->cachettl < $this->now) {
					header('HTTP/1.1 304 Not Modified');
					die;
				}
				// @codeCoverageIgnoreEnd
				//! load autoloaded classes' dictionaries and initialize the extensions one by one
				//! *** INIT Event ***
				foreach($this->libs as $k => $v) {
					self::lang($k);
					$c = @include_once("vendor/phppe/" . $k . "/config.php");
					if(method_exists($v, "init") && $v->init(is_array($c) ? $c : []) === false)
						unset($this->libs[ $k ]);
				}
				//! load application dictionary overrides
				self::lang("app");

				//! if not included as a library, run application
				if(! $islib) $this->run();
			}

		}

		// @codeCoverageIgnoreStart
/**
 * run diagnostics and try to fix errors
 */
		private function bootdiag()
		{
			//! we'll need some information from the client
			Core::$client=new Client;
			Core::$client->init();
			ini_set( "display_errors", 1 );
			//! extensions checks and webserver group id
			if(! empty($_SERVER[ 'argv' ][ 2 ]) && z($_SERVER[ 'argv' ][ 2 ], 0, 6) == "--gid=")
				$g[ "g" ] = intval(substr($_SERVER[ 'argv' ][ 2 ], 6));
			elseif(function_exists("posix_getpwnam"))
				foreach([ "www", "_www", "www-data", "http", "httpd", "apache", "nginx" ] as $n) {
					$g = posix_getpwnam($n);
					if(! empty($g[ "gid" ])) {
						$g[ "g" ] = $g[ "gid" ];
						break;
					}
				}
			//! fallback to 33 if not found or configured
			if(empty($g[ "g" ]))
				self::$g = 33;
			else
				self::$g = $g[ "g" ];
			//! output UID and GID
			$U = fileowner(__FILE__);
			if($this->runlevel)
				echo("DIAG-I: uid $U gid " . self::$g . "\n");
			//! helper function to create files
			function i($c, $r, $f = 0, $a = 0640)
			{
				//! if not exists yet or creation is forced
				if(! file_exists($c) || $f) {
					echo("DIAG-A: $c\n");
					file_put_contents($c, $r);
				}
				//! change owner and group
				if(file_exists($c) && (! @chgrp($c, \PHPPE\Core::$g) || ! @chown($c, fileowner(__FILE__))))
					echo("DIAG-E: chown/chgrp $c\n");
				//! change access rights
				return ! @chmod($c, $a);
			}
			//! fix missing files and access rights
			$E = "";
			$C = 0750;
			$W = 0775;
			if(function_exists("posix_getuid") && posix_getuid() != 0)
				echo("DIAG-W: not root or no php-posix, chown/chgrp may fail!\n");
			//! create directory structure
			$o = umask(0);
			//! hide errors here, target may not exists or the symlink may be already there
			@symlink("vendor/phppe/Core", "phppe");
			//! directory skeleton
			$D = [	".tmp" => $W,
					"data" => $W,
					"app" => 0,
					"vendor" => 0755,
					"vendor/bin" => 0,
					"vendor/phppe" => 0,
					"vendor/phppe/Core" => 0,
					"vendor/phppe/Core/log" => $W,
					"vendor/phppe/Core/views" => 0,
					"public/images" => 0,
					"public/css" => 0,
					"public/js" => 0,
					"app/addons" => 0,
					"app/sql" => 0,
					"app/ctrl" => 0,
					"app/lang" => 0,
					"app/libs" => 0,
					"app/views" => 0 ];
			$A = [ "*", "*/*", "*/*/*", "*/*/*/*", "*/*/*/*/*" ];
			foreach(["data/","vendor/"] as $d)
				foreach($A as $v)
					$D += array_fill_keys(@glob($d . $v), 0);
			//! $D now has all installed files
			foreach($D as $d => $p) {
				if(!$p) $p=$C;
				//! exceptions, three dirs that needs to be writeable
				$x=in_array(substr($d, 0, 4),[".tmp", "data"]) || substr($d, 0, 21) == "vendor/phppe/Core/log";
				if(is_file($d)) {
					$P = fileperms($d) &0777;
					$p = $x ? 0660 : 0640;
				}
				else
				{
					if($x)$p=$W;
					if(! is_dir($d) && ! is_file($d)) {
						echo("DIAG-A: $d\n");
						if(! mkdir($d, $p))
							self::log("C", "creating $d", "diag");
					}
					$P = fileperms($d) &0777;
				}
				//! if detected and calculated access rights diff
				if($P != $p) {
					$E .= sprintf("\t%03o?\t%03o ", $P, $p) . "$d\n";
					@chmod($d, $p);
				}
				if(! @chgrp($d, self::$g) || ! @chown($d, $U))
					echo("DIAG-E: chown/chgrp $d\n");
			}
			//! hide errors here, symlink may be already there
			@symlink("../../app", "vendor/phppe/app");
			foreach([ "images", "css", "js" ] as $v) {
				if(! file_exists("app/$v"))
					echo("DIAG-A: app/$v\n");
				@symlink("../public/$v", "app/$v");
			}
			//! create files
			umask(0027);
			i("app/config.php", "");
			i("app/init.php", "<"."?php\n//! include your classes here (if any)\n//include_once(__DIR__.'/libs/...');\n\n//! set your routes here (if any)\n//\\PHPPE\\Http::route('myurl','myClass','myMethod');\n\n//! return service instance (if any)\n//return new myService;\n");
			i( "public/.htaccess","RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^(.*)\$ index.php/\$1\n");
			i("public/favicon.ico", "");
			i("vendor/phppe/Core/config.php", "");
			$U = "https://raw.githubusercontent.com/bztsrc/phppe3/master/";
			$D = "vendor/phppe/Core/views/";
			$e = ".tpl";
			$c = "<!dump core.req2arr('obj')>";
			i($D . "403$e", "<h1>403</h1><!=L('Access denied')>");
			i($D . "404$e", "<h1>404</h1><!=L('Not found')>: <b><!=core.url></b>");
			i($D . "frame$e", "<div id='content'><!app></div>");
			i($D . "index$e", "<h1>PHPPE works!</h1>Next step: install <a href='" . $U . "phppe3_core.tgz' target='_new'>PHPPE Pack</a>.<br/><br/><!if core.isTry()><div style='display:none;'>$c</div><!/if><div style='background:#F0F0F0;padding:3px;'><b>Test form</b></div><!form obj>Text<!field text obj.f0 - - - Example [a-z0-9]+> Pass<!field pass obj.f1> Num(100..999)<!field *num(100,999) obj.f2> Phone<!field phone obj.f3><!field check obj.f4 Check>  File<!field file obj.f5>  <!field submit></form><table width='100%'><tr><td valign='top' width='50%'><!dump _REQUEST><!dump _FILES></td><td valign='top'>$c</td></tr></table>\n");
			i($D . "login$e", "<!form login><div style='color:red;'><!foreach core.error()><!foreach VALUE><!=VALUE><br/><!/foreach><!/foreach></div><!field text id><!field pass pass><!field submit></form>");
			i($D . "maintenance$e", "<h1><!=L('Site is temporarily down for maintenance reasons.')></h1>");
			i("composer.json", "{\n\t\"name\":\"phppe3\",\n\t\"version\":\"1.0.0\",\n\t\"keywords\":[\"phppe3\",\"\"],\n\t\"license\":[\"LGPL-3.0+\"],\n\n\t\"type\":\"project\",\n\t\"repositories\":[\n\t\t{\"type\":\"composer\",\"url\":\"$U\"}\n\t],\n\t\"require\":{\"phppe/core\":\"3.*\"},\n\n\t\"scripts\":{\"post-update-cmd\":\"sudo php public/index.php --diag\"}\n}\n");
			i(".gitignore", ".tmp\nphppe\nvendor\n");
			if($E)
				self::log("E", "Wrong permissions:\n$E", "diag");
			//! *** DIAG Event ***
			Core::event("diag");
			umask($o);
			die("DIAG-I: OK\n");
		}
		
/**
 * execute a PHPPE application
 *
 * @param application name, if not specified, url routing will choose
 * @param action name, if not specified, default action routing will apply
 */
		function run($app = "", $ac = "")
		{
			//! rotate security tokens for form validation, save form name
			$c = sha1(url());
			$S = ! empty($_SESSION[ "pe_s" ][ $c ]) ? $_SESSION[ "pe_s" ][ $c ] : "";
			for($i = 1; $i <= 9; $i++ )
				if(isset($_REQUEST[ "pe_try" . $i ]) && ! empty($_REQUEST[ "pe_s" ]) && $_REQUEST[ "pe_s" ] == $S) {
					$this->try = $i;
					$this->form = ! empty($_REQUEST[ 'pe_f' ]) ? trim($_REQUEST[ 'pe_f' ]) : "";
					$_SESSION[ "pe_s" ][ $c ] = 0;
					break;
				}
			if(empty($_SESSION[ "pe_s" ][ $c ]))
				$_SESSION[ "pe_s" ][ $c ] = sha1(uniqid()."PHPPE".VERSION);
			//! get validators from previous view generation
			if(! empty($_SESSION[ "pe_v" ]))
				self::$v = $_SESSION[ "pe_v" ];
			$_SESSION["pe_v"] = [];

			//! initialize view layer
			View::init($this);

			if(empty($this->maintenance))
			{

				//! get application and action
				list($app,$ac,$args) = HTTP::urlMatch($app,$ac,$this->url);
				//! *** ROUTE Event ***
				list($app,$ac) = self::event("route", [ $app, $ac ]);

				//! a few basic security checks
				$c = $app . "_" . $ac;
				if(strpos($c, "..") !== false || strpos($c, "/") !== false || 
				   substr($app, - 4) == ".php" || substr($ac, - 4) == ".php")
					$app = $this->template = "403";
				//! default template
				else
					$this->template = $app."_".$ac;

				//! canonize application's controller class
				$cls = get_declared_classes();
				$appCls = $app;
				foreach($cls as $c)
				{
					if(substr($c,0,10)=="PHPPE\\Ctrl" &&
						strtolower(substr($c,-strlen($app)-1))=="\\".strtolower($app))
					{
						$appCls=$c;
						break;
					}
				}

				//! look for controller in a separate file
				if(!method_exists($appCls,$ac))
				{
					foreach([
						//! in app you can override extension's controller
						//! but application name mandatory
						"app/ctrl/".$app."_".$ac.".php",
						"app/ctrl/".$this->app."_".$this->action.".php",
						//! best practice: extension name equals app name
						//! and ctrl directory holds action.php
						@self::$paths[strtolower($app)]."/ctrl/".$ac.".php",
						//! in case an extension ships more applications
						@self::$paths[strtolower($app)]."/ctrl/".$app."_".$ac.".php",
						@self::$paths[strtolower($app)]."/ctrl/".$this->app."_".$this->action.".php",
						@self::$paths[strtolower($app)]."/ctrl/".$this->app.".php"
						] as $d){
						if(!empty($d) && file_exists($d))
						{
							//! get the first controller class declared in that file
							$cnt = count($cls);
							include_once($d);
							$cls2 = get_declared_classes();
							for($i=$cnt;$i<count($cls2);$i++)
							{
								if(substr($cls2[$i],0,10)=="PHPPE\\Ctrl")
								{
									$appCls=$cls2[$i];
									unset($cls2);
									break 2;
								}
							}
							unset($cls2);
							break;
						}
}
				}

				//! if still no application found
				if(!class_exists($appCls))
				{
					//! for CLI check if it's a cron job, or fail
					if(! self::$w)
					{
						//! *** CRON Event ***
						die($this->app=="cron"?
							Core::event("cron".ucfirst($this->action),0):
							"PHPPE-C: " . L($this->app . "_" . $this->action ." not found!") . "\n");
					}
					//! for CGI fallback to Content Server
					$appCls="PHPPE\\Content";
					$ac="action";
				}

				//! instantiate application
				$appObj = new $appCls;

				View::setPath(self::$paths[strtolower($app)]);
				View::assign("app",$appObj);

				//! Application constructor may altered template, so we have to log this after "new App"
				self::log("D", $this->url . " ->$app::$ac " . $this->template, "routes");

				//! Check if page found in cache
				//! note that controller constructor may have turned cache off
				//! so Cache::get() will return a null
				$N = 'p_' . sha1($this->base.$this->url."/" . Core::$user->id . "/". Core::$client->lang);
				if(empty($this->nocache) && ! self::isTry())
					$T = View::fromCache($N);
				if(empty($T))
				{
					//! get frame meta data
					Content::getDDS($appObj);
					//! *** CTRL Event (Controller action) ***
					Core::event("ctrl", [ $app, $ac ]);
					//! call action method
					if(!method_exists($appObj, $ac))
						$ac="action";
					if(method_exists($appObj, $ac))
						! empty($args) ? call_user_func_array([ $appObj, $ac ], $args) : $appObj->$ac($this->item);
					$T = View::generate($this->template, $N);
				}
			} else {
				session_destroy();
				//! site is down message
				$T = View::template("maintenance");
				//! if no template found
				if(empty($T))
					$T = L("Maintenance mode is on");
			}
			//! check dump argument here, by now all core properties are populated
			if((@in_array("--dump", $_SERVER[ 'argv' ]) || isset($_REQUEST[ '--dump' ])) && $this->runlevel > 1)
				View::dump();

			//! close all database connections before output
			DS::close();

			//! *** VIEW Event ***
			$T = Core::event("view", $T);
			View::output($T);

			//! make sure to flush session
			session_write_close();
		}
		// @codeCoverageIgnoreEnd

/*** Core library ***/
/**
 * List all registered libraries (extensions)
 * @usage lib()
 * @param name, optional
 * @return array of library instances
 *
 * Query a library instance
 * @usage lib(n)
 * @param name, optional
 * @return library instance or null
 *
 * Define a new library
 * @usage lib(n,o,d)
 * @param name
 * @param object instance
 * @param dependency, optional
 */
		static function lib($n = "",$o=null,$d="")
		{
			$L = &self::$core->libs;
			if(empty($o))
			{
				//! return list of lib or a specific module instance
				return empty($n) ? $L : (empty($L[$n])?null:$L[ $n ]);
			} else {
				//! check dependencies
				$f = "";
				if($d) {
					if(!is_array($d))
						$d = Core::x(",", $d);
					foreach($d as $v)
						if(! self::isInst($v))
							$f .= ($f ? "," : "") . $v;
					if($f)
						// @codeCoverageIgnoreStart
						self::log("C", "$n depends on: $f");
						// @codeCoverageIgnoreEnd
				}
				//! add to list
				if(empty(self::$core->libs[ $n ]))
					self::$core->libs[ $n ] = is_object($o) ? $o : (class_exists($o)?new $o:new Extension);
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
/*
				//! save current context
				$S = [ self::$core->addons, self::$core->js, self::$core->jslib, self::$core->css ];
				//! return all available add-ons
				$d = @glob(N . "* /addons/*" . PE);
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
*/
				return self::$core->addons;
			}
			//! register an add-on with dependency check
			$d = "";
			//! check dependencies
			if($D) {
				if(! is_array($D))
					$D = Core::x(",", $D);
				foreach($D as $v)
					if(! self::isInst($v))
						$d .= ($d ? "," : "") . $v;
			}
			if(empty($d)) {
				Core::lang($n);
				self::$core->addons[ $n ] = [
					'name' => L(empty($l) ? "addon $n" : $l),
					'conf' => $c
				];
			}
			else
				self::log("E", "$n depends on: $d");
		}

/**
 * checks if an extension or an add-on is installed or not
 *
 * @param name
 * @return boolean true or false
 */
		static function isInst($n)
		{
			//! check for installed module or..
			return (isset(self::$core->libs[ $n ]) ||
			//! ...available addon. The latter maybe not loaded yet
				isset(self::$core->addons[ $n ]) || class_exists("\\PHPPE\\AddOn\\".$n) ||
				! empty(@glob("vendor/phppe/*/addons/" . $n . ".php")[ 0 ]));
		}

/**
 * Load a new language dictionary into memory
 *
 * @param class name (module name)
 */
		static function lang($c = "")
		{
			//! failsafe
			$L = empty(self::$client->lang) ? "en" : self::$client->lang;
			//! expand language dictionary
			$i = explode("_", $L);
			//! get translations
			$la = null;
			$c = "vendor/phppe/$c/lang/";
			//! first check as is, then first part, finally English
			//! eg.: hu_HU, hu, en; en_US, en
			foreach(array_unique([ $L, $i[ 0 ], "en" ]) as $l) {
				if(file_exists($c . $l . ".php")) {
					$la = include_once($c . $l . ".php");
						break;
				}
			}
			//! merge into dictionary
			if(is_array($la))
				self::$l = array_merge(self::$l, $la);
		}

/**
 * Log a message
 *
 * @param weight, Debug | Info | Audit | Warning | Error | Critical
 * @param message in English (don't translate it for compatibility)
 * @param module name, guessed if not given
 */
		static function log($w, $m, $n = null)
		{
			//! log a message of weight for a module
			$w = strtoupper($w);
			if(!in_array($w, [ "D", "I", "A", "W", "E", "C" ]))
				$w = "A";
			if(! is_string($m) || self::$core->runlevel < 3 && $w == "D")
				return;
			if(empty($n))
				$n = !empty(self::$core->app) ? self::$core->app : "core";
			//! remove sensitive information from message
			$m = trim(strtr($m,[dirname(dirname(__FILE__))."/"=>""]));
			$g = ! empty(self::$l[ $m ]) ? L($m) : $m;
			//! debug trace
			$t = "";
			if(! empty(self::$core->trace)) {
				$s = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				foreach($s as $d)
					$t .= "\t".substr(@$d[ 'file' ], strlen(dirname(__DIR__)) + 1). ":" .
					@$d[ 'line' ] . ":" . $d[ 'function' ] . "\n";
			}
			//! log always stores dates in UTC to be comparable among servers
			date_default_timezone_set("UTC");
			$p = date("Y-m-d") . "T" . date("H:i:s") . "Z-$w-" . strtoupper($n) . ": ";
			//! restore timezone
			@date_default_timezone_set(Core::$client->tz);
			//! send message syslog
			if(!empty(self::$core->syslog))
				syslog($w == "C" ? LOG_ERR : LOG_NOTICE, self::$core->base . ":PHPPE-$w-" . strtoupper($n) . ": "
				 . strtr($m, [ "\n" => "\\n", "\r" => "\\r" ]) . strtr($t, ["\n"=>""]));
			else {
				//! save message to file
				$l = "vendor/phppe/Core/log/" . $n . ".log";
				if( ! @file_put_contents($l, $p . 
					strtr($m, [ "\n" => "\\n", "\r" => "\\r" ]) . "\n" . $t, FILE_APPEND | LOCK_EX)) {
					// @codeCoverageIgnoreStart
					$w = "C";
					$g .= (! self::$w ? "\nLOG-C" : "<br/>\n" . date("Y-m-d") . "T" . date("H:i:s") . "Z-C-LOG") . ": " . L("unable to write") . " $l";
					// @codeCoverageIgnoreEnd
				}
				@chmod($l,0660);
				//! on critical message, bail out
			}
			if($w == "C") {
				// @codeCoverageIgnoreStart
				if(@self::$core->output != "html")
					die(strtoupper("$n-C") . ": " . $g . "\n" . $t);
				die("\n<html><body style='margin:8px;background:#000000;color:#A00000;'><div style='text-align:center;font-size:28px;color:#ff0000;'>PHPPE" . VERSION . " - " . L("Developer Console") . "</div><br/><br/>\n$p" . nl2br( strtr("$g\n$t", ["\t"=>"&nbsp;&nbsp;"])) . "</body></html>\n");
				// @codeCoverageIgnoreEnd
			}
			elseif(! self::$w && $w != "D" && $w != "I")
				fwrite(STDERR, strtoupper("$n-$w") . ": " . $g . "\n");
			return true;
		}

/**
 * Do security check if user tries to save a form
 *
 * @return boolean true or false
 */
		static function isTry($f = "")
		{
			//! return button number if user tries to save a form
			return empty($f) || $f == self::$core->form ? self::$core->try : 0;
		}

/**
 * Query all error messages
 * @usage Core::error()
 * @return array of messages groupped by fields
 * 
 * Add an error message to output
 *
 * @param message
 * @param if message is related to a field, it's name
 */
		static function error($m="", $f = "")
		{
			if(empty($m))
				return self::$core->error;
			//! register an error message
			if(! isset(self::$core->error[ $f ]))
				self::$core->error[ $f ] = [];
			self::$core->error[ $f ][ trim($m) ] = trim($m);
			//log validation error in developer and debug mode
//			if(self::$core->runlevel > 1)
//				self::log("E", $f . "@" . $_SERVER[ 'REQUEST_URI' ] . " " . $m, "validate");
		}

/**
 * Check for errors
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
 * Trigger an event
 * 
 * @param event name
 * @param context
 * @return modified context or null if not modified
 */
		static function event($e, $c = [])
		{
			foreach(Core::$core->libs as $k => $v)
				if(method_exists($v, $e)) {
					$d = call_user_func_array([ $v, $e ], is_array($c)?$c:[$c]);
					if($d!=null)
						$c=$d;
				}
			return $c;
		}

/*** Data layer ***/
/**
 * convert human readble php ini value to bytes
 *
 * @param php ini variable with units
 * @return in bytes
 */
		static function toBytes($i)
		{
			$v = trim(ini_get($i));
			$l = strtolower($v[ strlen($v) - 1 ]);
			switch($l) {
				case 't' : $v *= 1024;
				case 'g' : $v *= 1024;
				case 'm' : $v *= 1024;
				case 'k' : $v *= 1024;
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
			if(method_exists("\\PHPPE\\Addon\\". $v, "validate")||$v=="check"||$v=="file")
				self::$v[ $f ][ $v ] = [ ! empty($r), $a, $t ];
		}
/**
 * user request to object. Validates user input and returns an stdClass
 *
 * @param form prefix (request name)
 * @param validator data (if given, ovverrides templater's validator list)
 * @return form fields in stdClass
 */
		static function req2obj($p, $V = [])
		{
			//! same as request 2 object
			return self::req2arr($p, $V, 0);
		}
/**
 * user request to array. Validates user input and returns an array
 *
 * @param form prefix (request name)
 * @param validator data (if given, overrides templater's validator list)
 * @return form fields in array
 */
		static function req2arr($p, $V = [], $a = 1)
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
				if(substr($K, 0, strlen($p) + 1) == $p . ".") {
					$d = substr($K, strlen($p) + 1);
					$r = $p . "_" . $d;
					foreach($v as $T => $C) {
						//! browsers do not send false for checkboxes
						if(($T == "check" || ! empty($C[ 0 ])) && empty($R[ $r ]))
							$R[ $r ] = $T == "check" ? false : "";
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
				if(substr($k, 0, strlen($p) + 1) == $p . "_" && $k[ strlen($k) - 2 ] != ":") {
					$d = substr($k, strlen($p) + 1);
					$K = $p . "." . $d;
					if(isset($V[ $K ])) {
						//iterate on validators for this key
						foreach($V[ $K ] as $T => $C) {
							$t = "\\PHPPE\\AddOn\\".$T;
							if($T == "check")
								$v = $v ? ($v == 1 || $v == '1' ? true : $v) : false;
							if(! empty($C[ 0 ]) && empty($v)) {
								$v=null;
								self::error(L(ucfirst($d)) . " " . L("is a required field."), $K);
							}
							elseif(! empty($v) && method_exists($t, "validate")) {
								list($r, $m) = $t::validate($K, $v, $C[ 1 ], $C[ 2 ]);
								if(! $r && $m) { list($O, $f) = explode(".", $K);
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
		static function obj2str($o, $s = "", $c = " ")
		{
			return self::arr2str($o, $s, $c);
		}
		static function arr2str($o, $s = "", $c = " ")
		{
			//! get skip list
			if(! is_array($s))
				$s = Core::x(",", $s);
			//! iterate on fields
			$r = "";
			$d = DS::db();
			if(is_string($o)) $o = [ $o ];
			foreach($o as $k => $v) {
				if(! in_array($k, $s))
					$r .= ($r ? $c : "") . $k . "=" .
					($c == "," && ! empty($d) ? $d->quote($v) :
					"'" . str_replace([ "\r", "\n", "\t", "\x1a" ], [ "\\r", "\\n", "\\t", "\\x1a" ], addslashes($v)) . "'");
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
			if(is_array($s))
				return $s;
			elseif($s != "") {
				//! get value of variable
				$v = View::getval($s);
				//! if returned value is already an array
				if(is_array($v))
					return $v;
				//! if not, explode string
				return Core::x($c, $v);
			}
			return [];
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
						$c = strtolower($k) == "name" && ! $s ? $P . $w . $S : $w;
						if(is_array($v))
							$d[ $i ][ $k ] = $c;
						elseif(is_object($v))
							@$d[ $i ]->$k = $c;
					}
				}
				//! get child items
				if(is_array($v) && ! empty($v[ "_" ]) || is_object($v) && ! empty($v->_)) {
					//! prefix
					if($s) {
						$c = "\n" . sprintf($p, $i);
						if(is_array($d[ $i ]) && isset($d[ $i ][ "name" ]))
							$d[ $i ][ "name" ] .= $c;
						elseif(is_object($d[ $i ]) && isset($d[ $i ]->name))
							$d[ $i ]->name .= $c;
					}
					//! recursive call to walk through children elements too
					$d = self::tre2arr(is_array($v) ? $v[ "_" ] : $v->_, $p, $s, $P . ($s ? "" : $p), ($s ? "" : $s) . $S, $d);
					//! suffix
					if($s) {
						$c = "\n" . $s;
						if(is_array($d[ count($d) - 1 ]) && isset($d[ count($d) - 1 ][ "name" ]))
							$d[ count($d) - 1 ][ "name" ] .= $c;
						elseif(is_object($d[ count($d) - 1 ]) && isset($d[ count($d) - 1 ]->name))
							$d[ count($d) - 1 ]->name .= $c;
					}
				}
			}
			return $d;
		}

/*** private helper functions ***/
		static function started()
		{
			return self::$started;
		}

/**
 * Check url against given filters
 *
 * @param filters or @ACEs
 * @return boolean
 */
		static function cf($c)
		{
			//! check filters
			if(! is_array($c))
				$c = explode(",", $c);
			if(! empty($c))
				foreach($c as $F) {
					$F = trim($F);
					$G = "PHPPE\\Filter\\$F";
					//! for each filter do...
					if(! empty($F) &&
						//! is starts with a '@', it's an ACE
						(($F[ 0 ] == '@' && ! self::$user->has(substr($F, 1))) ||
						//! otherwise run filter method
						($F[ 0 ] != '@' && !@ $G::filter()))) {
						return false;
					}
				}
			return true;
		}

		//! quote-safe explode
		static function x($a, $b)
		{
			$r=[];
			$q='';
			for($l=$i=0;$i<=strlen($b);$i++){
				$c=@$b[$i];
				if($q){
					if($c==$q)
						$q="";
					if($c=='\\')
						$i++;
				}
				elseif($c=='"'||$c=="'")
					$q=$c;
				elseif($c==$a||$c==''){
					while(@$b[$l]==$a)
						$l++;
					$r[]=substr($b,$l,$i-$l);
					$l=$i+1;
				}
			}
			return $r;
	//		return explode($a, $b);
		}

		//! calculate hash
		static function h($a, $b, $c = "")
		{
			return sha1($a . "|" . $b . "|" . $c);
		}
	
	}//class

/******* Bootstrap PHPPE *******/
	if(empty(\PHPPE\Core::$core))
		new \PHPPE\Core(! empty(debug_backtrace()));
	return \PHPPE\Core::$core;
}//namespace

namespace PHPPE\Cache {

	//! PHP APC support
	class APC {
	/**
	 * constructor
	 *
	 * @param url (constant "apc")
	 */
	 function __construct($c="") {
		ini_set("apc.enabled",1);
		if(!function_exists("apc_fetch") && !function_exists("apcu_fetch"))
			// @codeCoverageIgnoreStart
			\PHPPE\Core::log('C',L("no php-apc"),"cache");
			// @codeCoverageIgnoreEnd
	 }
	 function get($key) {
		 if(function_exists("apc_fetch")){$e="apc_exists";$f="apc_fetch";} else {$e="apcu_exists";$f="apcu_fetch";}
		 $v=$e($key)?$f($key):null;if(function_exists('gzinflate')) $d = json_decode(@gzinflate($v)); return !empty($d)?$d:$v; }
	 function set($key,$value,$compress=false,$ttl=0) {
		 if(function_exists("apc_store"))$s="apc_store";else$s="apcu_store";
		 return $s($key,$compress&&function_exists('gzdeflate')?gzdeflate(json_encode($value)):$value,$ttl); }
	}
	
	//! Plain file cache support
	class Files {
	/**
	 * constructor
	 *
	 * @param url (constant "files")
	 */
	 function __construct($c="") { @mkdir("data/cache",0770); @chmod("data/cache",0775); }
	 private function fn($key) { return "data/cache/".substr($key,0,2)."/".substr($key,2,2)."/".substr($key,4); }
	 function get($key) {
		if(strlen($key)<5) $key="0000".$key;
		$ttl=intval(@file_get_contents($this->fn($key).".ttl"));
		if(!file_exists($this->fn($key)) || ($ttl>0 && time()-filemtime($this->fn($key))>$ttl)) return null;
		$v = @file_get_contents($this->fn($key));
		if(function_exists('gzinflate')) $d = @gzinflate($v);
		return json_decode(!empty($d)?$d:$v,true);
	 }
	 function set($key,$value,$compress=false,$ttl=0) {
		if(strlen($key)<5) $key="0000".$key;
		@mkdir("data/cache/".substr($key,0,2),0775);
		@chmod("data/cache/".substr($key,0,2),0775);
		@mkdir("data/cache/".substr($key,0,2)."/".substr($key,2,2),0775);
		@chmod("data/cache/".substr($key,0,2)."/".substr($key,2,2),0775);
		if($ttl>0) @file_put_contents($this->fn($key).".ttl",$ttl);
		$v=json_encode($value);
		return file_put_contents($this->fn($key),$compress&&function_exists('gzdeflate')?gzdeflate($v):$v)>0?true:false;
	 }
	 function cronMinute($args)
	 {
		$files = glob("data/cache/*/*/*.ttl");
		foreach($files as $f) {
			$ttl = intval(@file_get_contents($f));
			$cf = substr($f,0,strlen($f)-4);
			if($ttl<1 || time()-filemtime($cf) >= $ttl) {
				unlink($f);
				unlink($cf);
			}
		}
	 }
	}
}//namespace

/*** Common routing filters ***/
namespace PHPPE\Filter {
	class get extends \PHPPE\Filter
	{
		static function filter()
		{
			return @$_SERVER[ 'REQUEST_METHOD' ] == "GET" ? true : false;
		}
	}
	class post extends \PHPPE\Filter
	{
		static function filter()
		{
			return @$_SERVER[ 'REQUEST_METHOD' ] == "POST" ? true : false;
		}
	}
	class loggedin extends \PHPPE\Filter
	{
		static function filter()
		{
			if(\PHPPE\Core::$user->id)
				return true;
			/* save request uri for returning after successful login */
			// @codeCoverageIgnoreStart
			\PHPPE\Core::redirect("login", 1);
		}
			// @codeCoverageIgnoreEnd
	}
	class csrf extends \PHPPE\Filter
	{
		static function filter()
		{
			return \PHPPE\Core::isTry();
		}
	}
}//namespace

/*** Built-in fields ***/
namespace PHPPE\AddOn {
	use \PHPPE\Core as Core;
	use \PHPPE\View as View;

/**
 * hidden field element
 *
 * @usage obj.field
 */
	class hidden extends \PHPPE\AddOn
	{
		function edit()
		{
			return "<input type='hidden' name='" . $this->fld . "' value='" . htmlspecialchars(trim($this->value)) . "'>";
		}
	}

/**
 * javascript button element
 *
 * @usage label onclickjs [cssclass]
 */
	class button extends \PHPPE\AddOn
	{
		function show()
		{
			return $this->edit();
		}
		function edit()
		{
			$t = $this;
			$a=$t->attrs;
			return "<button class='" . (! empty($a[ 1 ]) && $a[ 1 ] != "-" ? $a[ 1 ] : "button") . "' onclick=\"" . strtr(! empty($a[ 0 ]) && $a[ 0 ] != "-" ? $a[ 0 ] : "alert('" . L("No action") . "');", ["\""=>"\\\""]) . "\">" . L(! empty($t->name) ? $t->name : "Press me") . "</button>";
		}
	}

/**
 * form submit button element
 *
 * @usage [label [onclickjs [cssclass]]]
 */
	class update extends \PHPPE\AddOn
	{
		function edit()
		{
			$t = $this;
			$a=$t->attrs;
			return "<input class='" . (! empty($a[ 1 ]) && $a[ 1 ] != "-" ? $a[ 1 ] : "button") . "' name='pe_try" . View::tc() . "' type='submit' value=\"" . htmlspecialchars(L(! empty($t->name) ? $t->name : "Okay")) . "\"" . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? " onclick=\"return " . strtr($a[ 0 ], ["\""=>"\\\""]) . "\"" : "") . ">";
		}
	}

/**
 * text field element
 *
 * @usage (maxlen[,rows]) obj.field [onkeyupjs [cssclass [fakevalue [pattern]]]]
 */
	class text extends \PHPPE\AddOn
	{
		function show()
		{
			return htmlspecialchars($this->value);
		}
		function edit()
		{
			$t = $this;
			$a=$t->args;
			$b=$t->attrs;
			$v = trim($t->value);
			$D=(! empty($a[ 3 ]) ? " dir='ltr'" : "");
			if($v == "null")
				$v = "";
			if(! empty($a[ 1 ]) && $a[ 1 ] > 0) {
				if($a[ 0 ] > 0)
					View::js("pe_mt(e,m)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;return(c==8||c==46||o.value.length<=m);");
				return "<textarea" . @View::v($t, $b[ 1 ], $b[ 0 ]) . " rows='" . $a[ 1 ] . "'" . ($a[ 0 ] > 0 ? " onkeypress='return pe_mt(event," . $a[ 0 ] . ");'" : "") . "$D wrap='soft' onfocus='this.className=this.className.replace(\" errinput\",\"\")'>" . $v . "</textarea>";
			}
			return "<input" . @View::v($t, $b[ 1 ], $b[ 0 ], $a) . " type='text'" .
			(! empty($b[ 2 ]) && $b[ 2 ] != "-" ? " onkepup='" . $b[ 2 ] . "'" : "") .
			" onfocus='this.className=this.className.replace(\" errinput\",\"\")'" .
			(! empty($b[ 3 ]) && $b[ 3 ]!="-" ? " placeholder=\"" . htmlspecialchars(L(trim($b[ 3 ]))) . "\"" : "") .
			(! empty($b[ 4 ]) ? " pattern=\"" . trim($b[ 4 ]) . "\"" : "") .
			"$D value=\"" . htmlspecialchars($v) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			if(@$a[0]>0)
				$v=substr($v,0,$a[0]);
			return [
				empty($t[4]) || preg_match("/".$t[4]."/",$v),
				"not matches the requested format"
			];
		}
	}

/**
 * password field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class pass extends \PHPPE\AddOn
	{
		function show()
		{
			return "******";
		}
		function edit()
		{
			$t = $this;
			return "<input" . 
			@View::v($t, $t->attrs[ 0 ], "", $t->args) . 
			" type='password' value=\"" . htmlspecialchars(trim($t->value)) . 
			"\" onfocus='this.className=this.className.replace(\" errinput\",\"\")'>";
		}
		static function validate($n, &$v, $a, $t)
		{
			if(function_exists("\\PHPPE\\pass")) return \PHPPE\pass($n, $v, $a, $t);
			return [
				preg_match("/[0-9]/", $v) && preg_match("/[a-z]/i", $v) && strtoupper($v) != $v && strtolower($v) != $v && strlen($v) >= 6,
				"not a valid password! [a-zA-Z0-9]*6"
			];
		}
	}

/**
 * number element
 *
 * @usage (maxlen[,min,max]) obj.field [cssclass]
 */
	class num extends \PHPPE\AddOn
	{
		function show()
		{
			return htmlspecialchars($this->value);
		}
		function edit()
		{
			$a=$this->args;
			$t = "this.value";
			$b = 'o.className=o.className.replace(" errinput","")';
			$C = isset($a[1])?"if($t<" . $a[ 0 ] . ")$t=" . $a[ 0 ] . ";if($t>" . $a[ 1 ] . ")$t=" . $a[ 1 ] . ";":"";
			$r = "return";
			View::js("pe_on(e)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;$b;if(c==8||c==37||c==39||c==46)$r true;c=String.fromCharCode(c);if(c.match(/[0-9\\b\\t\\r\\-\\.\\,]/)!=null)$r true;else{o.className+=' errinput';setTimeout(function(){{$b};},200);$r false;}");
			return "<input" . @View::v($this, $this->attrs[ 1 ], $this->attrs[ 0 ]) . " style='text-align:right;' type='number' onkeypress='$r pe_on(event);' onkeyup='$t=$t.replace(\",\",\".\");' onfocus='".$C."if($t==\"\")$t=0;".strtr($b,["o."=>"this."]).";this.select();'" . (isset($a[ 1 ]) ? " onblur='$C' min='".$a[0]."' max='".$a[1]."'" : "") . " value=\"" . htmlspecialchars(trim($this->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			$r = floatval($v).""==$v.""?true:false;
			//p("/^[0-9\-][0-9\.]+$/", $v);
			$m = "not a valid number!";
			if($r && isset($a[ 1 ])) {
				if($v < $a[ 0 ]) {
					$r = false;
					$m = "not enough!";
					$v = $a[ 0 ];
				}
				if($v > $a[ 1 ]) {
					$r = false;
					$m = "too much!";
					$v = $a[ 1 ];
				}
			}
			$v=floatval($v);
			return[ $r, $m ];
		}
	}

/**
 * option list element
 *
 * @usage (size[,ismultiple]) obj.field options [skipids [onchangejs [cssclass]]]
 */
	class select extends \PHPPE\AddOn
	{
		function show()
		{
			return htmlspecialchars(is_array($this->value) ? implode(", ", $this->value) : $this->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$b = $t->args;
			$opts = ! empty($a[ 0 ]) && $a[ 0 ] != "-" ? View::getval($a[ 0 ]) : [];
			if(is_string($opts))
				$opts = Core::x(",", $opts);
			$skip = ! empty($a[ 1 ]) && $a[ 1 ] != "-" ? View::getval($a[ 1 ]) : [];
			if(is_string($skip))
				$skip = Core::x(",", $skip);
			if(! is_array($skip)) $skip = [];
			else $skip = array_flip($skip);
			if(!empty($b[1]))
				$t->name.="[]";
			$r = "<select" . @View::v($t, $a[ 3 ], $a[ 2 ]) . (! empty($b[ 1 ]) ? " multiple" : "") .
				(! empty($b[ 0 ]) && $b[ 0 ] > 0 ? " size='" . intval($b[ 0 ]) . "'" : "") .
				" onfocus='this.className=this.className.replace(\" errinput\",\"\")'>";
			if(is_array($opts))
				foreach($opts as $k => $v) {
					$o = is_array($v) && isset($v[ 'id' ]) ? $v[ 'id' ] : (is_object($v) && isset($v->id) ? $v->id : $k);
					$n = is_array($v) && !empty($v[ 'name' ]) ? $v[ 'name' ] : (is_object($v) && ! empty($v->name) ? $v->name : (is_string($v) ? $v : $k));
					if(! isset($skip[ $o ]) && ! empty($n))
						$r .= "<option value=\"" . htmlspecialchars($o) . "\"" . ((is_array($t->value) && in_array($o . "", $t->value)) || $o == $t->value ? " selected" : "") . ">" . $n . "</option>";
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
	class check extends \PHPPE\AddOn
	{
		function show()
		{
			$t = $this;
			return empty(Core::$core->output) || Core::$core->output != "html" ?
				("[" . (! empty($t->value) ? "X" : " ") . "] " . (! empty($t->attrs[ 0 ]) ? L($t->attrs[ 0 ]) : $t->value)) :
			htmlspecialchars($t->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$e = Core::isError($t->name);
			return($e ? "<span class='errinput'>" : "") .
			"<input" . @View::v($t, $a[ 2 ], $a[ 1 ]) . " id='" . $t->name . "' type='checkbox'" . (! empty($t->value) ? " checked" : "") . " value=\"" . htmlspecialchars(trim(! empty($t->args[ 0 ]) ? $t->args[ 0 ] : "1")) . "\">" .
			(! empty($a[ 0 ]) ? "<label for='" . $t->name . "'>" . L($a[ 0 ]) . "</label>" : "") .
			($e ? "</span>" : "");
		}
	}

/**
 * radiobutton elements
 *
 * @usage (value) obj.field [label [cssclass]]
 */
	class radio extends \PHPPE\AddOn
	{
		function show()
		{
			$t = $this;
			return empty(Core::$core->output) || Core::$core->output != "html" ?
			("(" . ($t->value == $t->args[ 0 ] ? "X" : " ") . ") " . (! empty($t->attrs[ 0 ]) ? L($t->attrs[ 0 ]) : $t->args[0])) :
			htmlspecialchars($t->value);
		}
		function edit()
		{
			$t = $this;
			$a = $t->args;
			$b = $t->attrs;
			return "<input" . @View::v($t, $b[ 2 ], $b[ 1 ]) . " id='" . $t->name . "_" . sha1($a[ 0 ]) . "' type='radio'" .
			($t->value == $a[ 0 ] ? " checked" : "") . " value=\"" . htmlspecialchars(trim(! empty($a[ 0 ]) ? $a[ 0 ] : "1")) . "\">" .
			(! empty($b[ 0 ]) ? "<label for='" . $t->name . "_" . sha1($a[ 0 ]) . "'>" . L($b[ 0 ]) . "</label>" : "");
		}
	}

/**
 * phone number field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class phone extends \PHPPE\AddOn
	{
		function show()
		{
			return htmlspecialchars($this->value);
		}
		function edit()
		{
			$t = $this;
			$b = 'o.className=o.className.replace(" errinput","")';
			View::js("pe_op(e)", "var c,o;if(!e)e=window.event;o=e.target;c=e.keyCode?e.keyCode:e.which;$b;if(c==8||c==37||c==39||c==46)return true;c=String.fromCharCode(c);if(c.match(/[0-9\\b\\t\\r\\-\\ \\+\\(\\)\\/]/)!=null)return true;else{o.className+=' errinput';setTimeout(function(){{$b};},200);return false;}");
			return "<input" . @View::v($t, $t->attrs[ 1 ], $t->attrs[ 0 ], $t->args, "[0-9\+][0-9\ \(\)\-]+") . " type='tel' onfocus='".strtr($b,["o."=>"this."])."' onkeypress='return pe_op(event);' value=\"" . htmlspecialchars(trim($t->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			return [
				preg_match("/^[0-9\+][0-9\ \(\)\-]+$/", $v),
				"invalid phone number"
			];
		}
	}

/**
 * email address field element
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class email extends \PHPPE\AddOn
	{
		function show()
		{
			if(empty(Core::$core->output) || Core::$core->output != "html")
				return $this->value;
			return strtr(htmlspecialchars($this->value), [ "@" => "&#64;", "." => "&#46;" ]);
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			$b = 'o.className=o.className.replace(" errinput","")';
			View::js("pe_oe(o)", "$b;if(o.value!=''&&o.value.match(/^.+\@(\[?)[a-z0-9\-\.]+\.([a-z]{2,3}|[0-9]{1,3})(\]?)$/i)==null)o.className+=' errinput';");
			return "<input" . @View::v($t, $a[ 1 ], "", $t->args) . " type='email' onfocus='".strtr($b,["o."=>"this."])."' onchange='pe_oe(this);" . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? $a[ 0 ] : "") . "' value=\"" . htmlspecialchars(trim($t->value)) . "\">";
		}
		static function validate($n, &$v, $a, $t)
		{
			return [
				preg_match("/^.+\@(\[?)[a-z0-9\-\.]+\.([a-z]{2,3}|[0-9]{1,3})(\]?)$/i", $v),
				"invalid email address"
			];
		}
	}

/**
 * file upload input box
 *
 * @usage (size[,maxlen]) obj.field [cssclass]
 */
	class file extends \PHPPE\AddOn
	{
		function show()
		{
			return "";
		}

		function edit()
		{
			$t = $this;
			$e = Core::isError($t->name);
			return($e ? "<span class='errinput'>" : "") .
				"<input" . @View::v($t, $t->attrs[ 0 ], $t->attrs[ 1 ], $t->args) . " type='file'>&nbsp;(" . round(Core::$fm / 1048576) . "Mb)" .
				($e ? "</span>" : "");
		}
	}

/**
 * colorpicker element
 *
 * @usage obj.field
 */
	class color extends \PHPPE\AddOn
	{
		function show()
		{
			return "span style='width:10px;height:10px;background-color:".$this->value.";'></span> ".$this->value;
		}
		function edit()
		{
			$t = $this;
			$a = $t->attrs;
			return "<input" . @View::v($t, $a[ 1 ], "", $t->args) . " type='color' " . (! empty($a[ 0 ]) && $a[ 0 ] != "-" ? " onchange='".$a[ 0 ]."'" : "") . " value=\"" . htmlspecialchars(trim($t->value)) . "\">";
		}
	}

}//namespace

namespace {

/*** I18N ***/
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
		return \PHPPE\Http::url($m, $p);
	}

}
?>