<?php
use PHPPE\Core as Core;

//L("Email")
class EmailTest extends PHPUnit_Framework_TestCase
{
	public function testEmail()
	{
		if(!\PHPPE\ClassMap::has("PHPPE\Email"))
			$this->markTestSkipped();

		\PHPPE\Core::$core->mailer=null;

		$email = new \PHPPE\Email;
		$emailData = $email->get();
		$email2 = new \PHPPE\Email($emailData);
		$this->assertNotEmpty($email2,"Empty Email dump");

		$wasExc=false;
		try {
			$email3 = new \PHPPE\Email("something");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"Email creating exception");

		$wasExc=false;
		try {
			$email->send();
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No backend exception");

		$wasExc=false;
		try {
			$email->send("db");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No message exception");

		$email->message("Something");
		$wasExc=false;
		try {
			$email->send("db");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No subject exception");

		$email->subject("Subject");
		\PHPPE\DS::close();
		$wasExc=false;
		try {
			$email->send("db");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No recipient exception");

		$wasExc=false;
		try {
			$email->to("me");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"Bad address exception #1");
		
		$wasExc=false;
		try {
			$email->to("me@notld");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"Bad address exception #2");

		$email->to("me@localhost");
		$email->to("me@localhost");
		$email->replyTo("me2@localhost");
		$email->cc("me3@localhost");
		$email->bcc("me4@localhost");
		$wasExc=false;
		try {
			$email->send("db");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No db exception");

		$wasExc=false;
		try {
			$email->send("phpmailer");
		} catch(\Exception $e) {$wasExc=true;}
		$this->assertTrue($wasExc,"No phpmailer exception");

		$this->assertNotEquals(0,
			preg_match(
				"/Message\-ID/",
				$email->send("mime")),
			"Return mime message");

		$email->attachFile("images/phppe.png");
		$email->attachFile("images/phppe.png","image/png");
		$email2->attachData("something.txt","text/plain","something");

		$mime = $email2->message("<html><body>html mail<img src='http://localhost/something.jpg'><img src='images/phppe.png'></body></html>")
			->subject("Subject")->to("me@localhost")->send("mime");

		$this->assertTrue($email2->send("log"),"Log backend");
		$email2->send("mail");
		$email2->send("sendmail");
		$email2->send("smtp://localhost:25");
		$email2->template("testemail", ["name"=>"something"]);

		$email3 = new \PHPPE\Email;
		$email3->to("me@localhost")->subject("Subject")->message("message");

		\PHPPE\DS::db("sqlite::memory:");
		$wasExc=false;
		try {
			$email->send("db");
			$email3->send("db");
		} catch(\Exception $e) {$wasExc=true;echo($e);}
		$this->assertFalse($wasExc,"To db queue");

		\PHPPE\Core::$core->realmailer="log";
		$email->cronMinute("");

		\PHPPE\Core::$core->realmailer="log";
		$email->cronMinute("");
	}
}
?>
