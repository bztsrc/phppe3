<?php
/**
 * Controller for Extension Manager
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;

class Extensions extends \PHPPE\Extensions {
	public $_favicon="images/phppeicon.png";

	function __construct ()
	{
		//! common check for all action handlers
		if(!Core::$user->has("install")) {
			Http::redirect("login", true);
		}

		//! if bootstrap extension not installed, use cdn version
		if(!Core::isInst("bootstrap"))
			View::css("http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css");

		View::css("extensions.css");
		View::jslib("sha256.js");
		View::jslib("extensions.js");

	}

/**
 * These actions are called via AJAX
 */
	function install( $item , $re="")
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::installPkg( $item ) );
	}

	function uninstall( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::uninstall( $item ) );
	}

	function bootstrap( $item="" )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::bootstrap($item) );
	}

	function getconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::getConf( $_REQUEST['item'] ) );
	}

	function setconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( parent::setConf( $_REQUEST['item'] ) );
	}

	function action($item)
	{
		Core::$core->template="";
		View::js("init()", "pe.extensions.init();", true);
	}
}
?>
