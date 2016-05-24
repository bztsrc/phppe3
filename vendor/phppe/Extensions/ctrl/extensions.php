<?php
/**
 * Controller for Extension Manager
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as Core;

class Extensions extends \PHPPE\Extensions {
	public $_favicon="images/phppeicon.png";

	function __construct ()
	{
		//! common check for all action handlers
		if(!Core::$user->has("install")) {
			\PHPPE\Http::redirect("login", true);
		}

		//! if bootstrap extension not installed, use cdn version
		if(!Core::isInst("bootstrap"))
			\PHPPE\View::css("http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css");

		\PHPPE\View::css("extensions.css");
		\PHPPE\View::jslib("sha256.js");
		\PHPPE\View::jslib( "extensions.js" );

	}

/**
 * These actions are called via AJAX
 */
	function install( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::install( $item ) );
	}

	function uninstall( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::uninstall( $item ) );
	}

	function bootstrap( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::bootstrap() );
	}

	function getconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::getconf( $item ) );
	}

	function setconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::setconf( $item ) );
	}

	function action($item)
	{
		Core::$core->template="";
		\PHPPE\View::js("init()", "extensions_init();", true);
	}
}
?>
