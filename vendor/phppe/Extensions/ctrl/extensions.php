<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Extensions extends \PHPPE\Ctrl {

	function __construct ($cfg)
	{
		if(!PHPPE::$user->has("install")) {
			PHPPE::redirect("login", true);
		}
		//load global remote config if user specific not found
		if( empty(PHPPE::$user->data['remote']['host']) ) {
			if( !empty($cfg['identity']) && !empty($cfg['user']) && !empty($cfg['host']) && !empty($cfg['path']) )
				PHPPE::$user->data['remote']=$cfg;
			else
				PHPPE::log("W","Unable to load global remote configuration from vendor/phppe/Extensions/config.php");
		}

		PHPPE::css("extensions.css");
		PHPPE::jslib("sha256.js");
		PHPPE::jslib( "extensions.js" );
	}

	function action_install( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( PHPPE::lib("Extensions")->install( $item ) );
	}

	function action_uninstall( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( PHPPE::lib("Extensions")->uninstall( $item ) );
	}

	function action_bootstrap( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( PHPPE::lib("Extensions")->bootstrap() );
	}

	function action_getconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( PHPPE::lib("Extensions")->getconf( $item ) );
	}

	function action_setconf( $item )
	{
		header( "Cache-Control: no-cache,no-store,private,must-revalidate" );
		header( "Content-Type: text/plain;charset=utf-8" );
		die( PHPPE::lib("Extensions")->setconf( $item ) );
	}

	function action($item)
	{
		PHPPE::$core->template="";
		PHPPE::js("init()", "extensions_init();", true);
	}
}
?>