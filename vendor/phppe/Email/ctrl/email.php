<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Email extends \PHPPE\Ctrl {

	function action($item)
	{
		//! check if called from CLI
		if( PHPPE::$client->ip != "CLI" ) {
			PHPPE::$core->template = "404";
			return;
		}
		//! get real mailer backend ($core->mailer points to db queue backend)
		if( empty(PHPPE::$core->realmailer) )
			PHPPE::log('C', "Real mailer backend not configured!");
		PHPPE::$core->mailer = PHPPE::$core->realmailer;

		//! get items from database
		while( $row = PHPPE::fetch("*", "email_queue", "", "", "id ASC") ) {
			$email = new Email($row['data']);
			try {
				if(!$email->send())
					throw new \Exception("send() returned false");
				PHPPE::exec("DELETE FROM email_queue WHERE id=?;", [$row['id']]);
			} catch(\Exception $e) {
				PHPPE::log('E', "Unable to send #".$row['id']." from queue: ".$e->getMessage());
			}
			sleep(1);
		}
		die();
	}
}
?>