<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/.
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
 * @file vendor/phppe/Core/libs/Email.php
 *
 * @author bzt
 * @date 1 Jan 2016
 * @brief Email message object, included in Pack
 */

namespace PHPPE;

/**
 * Exception class.
 */
class EmailException extends \Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class Email extends Extension
{
    public $name;
    //! smtp relay parameters
    private $via;
    private static $host;
    private static $port;
    private static $user;
    private static $pass;
    private static $sender;
    private static $forge;

    //! email object properties
    private $header;
    private $message;
    private $attach;

    /**
     * constructor. You can pass a previosly dumped object to it.
     *
     * @param object data dumped by get()
     * @param string hostname
     */
    public function __construct($msg = '')
    {

        //! use Core's smtp relay configuration
        //! we allow an array here, to specify additional fields in config.php
        $cfg = is_array(Core::$core->mailer) ? Core::$core->mailer : @parse_url(Core::$core->mailer);
        $this->getBackEnd($cfg);

        //! load data from dumped object
        if (!empty($msg)) {
            $msg = json_decode($msg, true);
            if (json_last_error() || !is_array($msg)) {
                throw new EmailException(json_last_error_msg());
            }
            foreach (['header', 'message', 'attach'] as $k) {
                $this->$k = $msg[$k];
            }
        }
    }

    private function getBackEnd($cfg)
    {
        //! populate properties
        $p = !empty($cfg['protocol']) ? $cfg['protocol'] : (!empty($cfg['scheme']) ? $cfg['scheme'] : '');
        if (empty($p) && is_string($cfg)) $p = $cfg;
        if (empty($p) && !empty($cfg['path'])) $p = $cfg['path'];
        $this->via = !empty($p) && in_array($p, [
            //! backends
            'smtp',        //! speak smtp directly (no dependency at all, default)
            'mime',        //! just build mime message and return it
            'log',        //! only log message but do not send it for real
            'mail',        //! use php's mail()
            'sendmail',    //! use sendmail command through pipe
            'db',        //! store message in database queue
            'phpmailer',    //! use PHPMailer class for sending
        ]) ? $p : '';
        self::$host = !empty($cfg['host']) ? $cfg['host'] : 'localhost';
        self::$port = !empty($cfg['port']) ? $cfg['port'] : 25;
        self::$user = !empty($cfg['user']) ? $cfg['user'] : '';
        self::$pass = !empty($cfg['pass']) ? $cfg['pass'] : '';
        self::$sender =
            !empty($cfg['sender']) ? $cfg['sender'] :
            'no-reply@'.(!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] :
                (!empty(Core::$core->hostname) ? Core::$core->hostname : 'localhost'));
        self::$forge = !empty($cfg['forge']) ? $cfg['forge'] : '';
    }
    /**
     * you can call this to get email in a form that can be stored in database.
     *
     * @return dumped email object
     */
    public function get()
    {
        return json_encode([
            'header' => $this->header,
            'message' => $this->message,
            'attach' => $this->attach,
        ]);
    }

    /**
     * methods to add headers to email.
     */
    public function to($email)
    {
        $this->address($email, 'To');

        return $this;
    }
    public function replyTo($email)
    {
        $this->address($email, 'Reply-to');

        return $this;
    }
    public function cc($email)
    {
        $this->address($email, 'Cc');

        return $this;
    }
    public function bcc($email)
    {
        $this->address($email, 'Bcc');

        return $this;
    }
    public function subject($subject)
    {
        $this->header['Subject'] = '=?utf-8?Q?'.quoted_printable_encode(str_replace("\r", '', str_replace("\n", ' ', $subject))).'?=';

        return $this;
    }
    public function message($message)
    {
        $this->message = trim(str_replace("\r", '', $message));

        return $this;
    }
    public function template($template, $args)
    {
        \PHPPE\View::assign('args', $args);
        $this->message = trim(str_replace("\r", '', \PHPPE\View::template($template)));

        return $this;
    }
    /**
     * methods to add attachments to email.
     */
    public function attachFile($file, $mime = '')
    {
        if (!empty($file)) {
            $this->attach[] = [
                'file' => $file,
                'mime' => !empty($mime) && strpos($mime, '/') ? $mime : 'application/octet-stream',
            ];
        }

        return $this;
    }
    public function attachData($data, $mime = '', $filename = '')
    {
        if (!empty($data)) {
            $this->attach[] = [
                'file' => !empty($filename) ? $filename : '',
                'mime' => !empty($mime) && strpos($mime, '/') ? $mime : 'application/octet-stream',
                'data' => $data, ];
        }

        return $this;
    }
    /**
     * construct mime email and send it out.
     */
    public function send($via = '')
    {
        //! allow temporarly override backend. Only url allowed, not array
        if (!empty($via)) {
            $this->getBackEnd(@parse_url($via));
        }
        //! sanity checks
        if (empty($this->via)) {
            throw new EmailException(L('Mailer backend not configured!'));
        }
        if (empty($this->message)) {
            throw new EmailException(L('Empty message!'));
        }
        if (empty($this->header['Subject'])) {
            throw new EmailException(L('No subject given!'));
        }
        if (empty($this->header['To'])) {
            throw new EmailException(L('No recipient given!'));
        }
        if (count($this->header['To']) > 64) {
            // @codeCoverageIgnoreStart
            throw new EmailException(L('Too many recipients!'));
        }
            // @codeCoverageIgnoreEnd

        $this->address(self::$sender, 'From');
        $local = @explode('@', array_keys($this->header['From'])[0])[1];
        if (empty($local)) $local = 'localhost';

        $id = sha1(uniqid()).'_'.microtime(true).'@'.$local;
        //! message type
        $isHtml = preg_match('/<html/i', $this->message);

        //! *** handle backends that does not require mime message ***
        if ($this->via == 'db') {
            //! mail queue in database
            if (empty(DS::db())) {
                throw new EmailException(L('DB queue backend without configured datasource!'));
            }

            return DS::exec('INSERT INTO email_queue (data,created) VALUES (?,?);', [$this->get(), \PHPPE\Core::$core->now]) > 0 ? true : false;
        } elseif ($this->via == 'phpmailer') {
            //! PHP Mailer
            if (!\PHPPE\ClassMap::has('PHPMailer')) {
                throw new EmailException(L('PHPMailer not installed!'));
            }
            // @codeCoverageIgnoreStart
            $mail = new \PHPMailer();
            $mail->Subject = $this->header['Subject'];
            $mail->SetFrom(implode(', ', $this->header['From']));
            if (!empty($this->header['Reply-To'])) {
                $mail->AddReplyTo($this->header['Reply-To']);
            }
            foreach (['To', 'Cc', 'Bcc'] as $type) {
                foreach ($this->header[$type] as $rcpt => $full) {
                    list($name) = explode('<', $full);
                    $mail->SetAddress(self::$forge ? self::$forge : $rcpt, trim($name));
                }
            }
            foreach ($this->attach as $attach) {
                $mail->AddAttachment($attach['file']);
            }
            $mail->MsgHTML($this->message);

            return $mail->Send();
            // @codeCoverageIgnoreEnd
        }

        //! *** build mime message ***
        //! mime headers
        $headers['MIME-Version'] = '1.0';
        $headers['Content-Class'] = 'urn:content-classes:message';
        $headers['Content-Type'] = 'text/plain;charset=utf-8';
        $headers['Content-Transfer-Encoding'] = '8bit';
        $headers['Sender'] = implode(', ', $this->header['From']);
        $headers['Message-ID'] = '<'.$id.'>';
        $headers['Date'] = date('r', Core::$core->now);
        $headers['X-Mailer'] = 'PHPPE '.VERSION;
        foreach ($this->header as $k => $v) {
            $headers[$k] = is_array($v) ? implode(', ', $v) : $v;
        }

        //! mime body
        if (!$isHtml) {
            //! plain text email
            $message = wordwrap($this->message, 78);
        } else {
            $boundary = uniqid();
            //! html email with a plain text alternative
            $headers['Content-Type'] = "multipart/alternative;\n boundary=\"".$boundary.'"';
            $message = "This is a multi-part message in MIME format.\r\n";

            $message .= '--'.$boundary."\n".
                "Content-type: text/plain;charset=utf-8\n".
                "Content-Transfer-Encoding: 8bit\n\n".
                wordwrap(
                preg_replace("/\<.*?\>/m", '',
                strtr($this->message, [
                    '</h1>' => "\n\n",
                    '</h2>' => "\n\n",
                    '</h3>' => "\n\n",
                    '</h4>' => "\n\n",
                    '</h5>' => "\n\n",
                    '</h6>' => "\n\n",
                    '</p>' => "\n\n",
                    '</td>' => "\t",
                    '</tr>' => "\n",
                    '</table>' => "\n",
                    '<br>' => "\n",
                    '<br/>' => "\n",
                ])), 78)."\r\n";
            //! look for images in html, if found, we have to create a multipart/related block
            if (preg_match_all("/(http|images\/|data\/).*?\.(gif|png|jpe?g)/mis", $this->message, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                $boundary2 = uniqid();
                $diff = 0;
                foreach ($m as $k => $c) {
                    //if it's a absolute url, don't replace it
                    if ($c[1][0] == 'http') {
                        unset($m[$k]);
                        continue;
                    }
                    //generate a cid
                    $m[$k][3] = uniqid();
                    //get local path for filename
                    if ($c[1][0] == 'data/' && file_exists($c[0][0])) $m[$k][4] = $c[0][0];
                    elseif (file_exists('public/'.$c[0][0])) $m[$k][4] = 'public/'.$c[0][0];
                    else {
                        foreach (['vendor/phppe/*/', 'vendor/*/', 'vendor/*/*/'] as $d) {
                            if ($m[$k][4] = @glob($d.$c[0][0])[0]) {
                                break;
                            }
                        }
                    }
                    //replace image url in message
                    $new = 'cid:'.$m[$k][3];
                    $this->message =
                        substr($this->message, 0, $c[0][1] + $diff).
                        $new.
                        substr($this->message, $c[0][1] + $diff + strlen($c[0][0]));
                    $diff -= strlen($c[0][0]) - strlen($new);
                }
            }
            if (!empty($m)) {
                //! add the html part as related
                $message .= '--'.$boundary."\n".
                    "Content-type: multipart/related;\n boundary=\"".$boundary2."\"\n\n".
                    "This is a multi-part message in MIME format.\r\n--".$boundary2."\n".
                    "Content-Type: text/html;charset=utf-8\n".
                    "Content-Transfer-Encoding: 8bit\n\n".
                    wordwrap($this->message, 78)."\r\n";
                foreach ($m as $c) {
                    $data = empty($c[4]) ? '' : (substr($c[4], 0, 4) == 'http' ? Core::get($c[4]) : file_get_contents($c[4]));
                    if (!$data) continue;
                    //get content
                    $message .= '--'.$boundary2."\n".
                        'Content-Type: image/'.($c[2][0] == 'jpg' ? 'jpeg' : $c[2][0])."\n".
                        "Content-Transfer-Encoding: base64\n".
                        "Content-Disposition: inline\n".
                        'Content-ID: <'.$c[3].">\n\n".
                        chunk_split(base64_encode($data), 78, "\n");
                }
                $message .= '--'.$boundary2."--\n";
            } else {
                $message .= '--'.$boundary."\n".
                    "Content-type: text/html;charset=utf-8\n".
                    "Content-Transfer-Encoding: 8bit\n\n".
                    wordwrap($this->message, 78)."\r\n";
            }
            $message .= '--'.$boundary."--\n";
        }
        if (!empty($this->attach)) {
            $boundary = uniqid();
            $headers['Content-Type'] = "multipart/mixed;\n boundary=\"".$boundary.'"';
            $message = "This is a multi-part message in MIME format.\r\n--".$boundary."\n".$message;
            foreach ($this->attach as $attach) {
                $data = !empty($attach['data']) ? $attach['data'] : (substr($attach['file'], 0, 4) == 'http' ? Core::get($attach['file']) : file_get_contents(substr($attach['file'], 0, 6) == 'images' ? @glob('vendor/phppe/*/'.$attach['file'])[0] : $attach['file']));
                if (!$data) {
                    continue;
                }
                $message .= '--'.$boundary."\n".
                    'Content-type: '.(!empty($attach['mime']) ? $attach['mime'] : 'application-octet-stream')."\n".
                    'Content-Disposition: attachment'.(!empty($attach['file']) ? ";\n filename=\"".basename($attach['file']).'"' : '')."\n".
                    "Content-Transfer-Encoding: base64\n\n".
                    chunk_split(base64_encode($data), 78, "\n");
            }
            $message .= '--'.$boundary."--\n";
        }
        //! flat headers and remove trailer from message
        $header = '';
        if (!empty(self::$forge)) {
            // @codeCoverageIgnoreStart
            $headers['To'] = self::$forge;
            $headers['Cc'] = '';
            $headers['Bcc'] = '';
            // @codeCoverageIgnoreEnd
        }
        foreach ($headers as $k => $v) {
            $header .= $k.': '.$v."\r\n";
        }

        //! log that we are sending a mail
        Core::log('I', 'To: '.$headers['To'].', Subject: '.$headers['Subject'].', ID: '.$id, 'email');
        //if email directory exists, save the full mime message as well for debug
        @file_put_contents('phppe/log/email/'.$id, 'Backend: '.$this->via.' '.self::$user.':'.self::$pass.'@'.self::$host.':'.self::$port."\r\n\r\n".$header."\r\n".$message);

        //! *** handle backends ***
        switch ($this->via) {
            //! only save message in file, do not send for real
            case 'log': break;
            //! return constructed mime message
            case 'mime': return $header."\r\n".$message; break;
            //! use php mail()
            case 'mail': {
                $to = $headers['To'];
                $subj = $headers['Subject'];
                unset($headers['To']);
                unset($headers['Subject']);
                $header = '';
                foreach ($headers as $k => $v) {
                    $header .= $k.': '.$v."\r\n";
                }
                if (!mail($to, $subj, $message, $header)) {
                    Core::log('E', 'mail() failed, To: '.$to.', Subject: '.$subj.', ID: '.$id, 'email');

                    return false;
                }
            // @codeCoverageIgnoreStart
            } break;
            //! sendmail through pipe
            case 'sendmail': {
                $f = @popen('/usr/sbin/sendmail -t -i', 'w');
                if ($f) {
                    fputs($f, $header."\r\n".$message);
                    pclose($f);
                } else {
                    Core::log('E', 'mail() failed, To: '.$headers['To'].', Subject: '.$headers['Subject'].', ID: '.$id, 'email');

                    return false;
                }
            } break;
            //! this is how real programmers do it, let's speak smtp directrly!
            default: {
                //open socket
                $s = @fsockopen(self::$host, self::$port, $en, $es, 5);
                $l = '';
                //get welcome message
                if ($s) {
                    stream_set_timeout($s, 5);
                    $l = fgets($s, 1024);
                }
                if (!$s || substr($l, 0, 3) != '220') {
                    Core::log('E', 'connection error to '.self::$host.':'.self::$port.', '.trim($l), 'email');

                    return false;
                }
                //we silently assume we got 8BITMIME here, it's a safe assumption
                while ($l[3] == '-') {
                    $l = fgets($s, 1024);
                }
                //greet remote
                fputs($s, 'EHLO '.$local."\r\n");
                $l = fgets($s, 1024);
                while ($l[3] == '-') {
                    $l = fgets($s, 1024);
                }
                //tell who are sending
                fputs($s, 'MAIL FROM:'.array_keys($this->header['From'])[0]."\r\n");
                $l = fgets($s, 1024);
                if (substr($l, 0, 3) != '250') {
                    PPHPE3::log('E', 'from error: '.trim($l), 'email');

                    return false;
                }
                //to whom
                $addresses = array_merge(array_keys($this->header['To']), array_keys($this->header['Cc']), array_keys($this->header['Bcc']));
                foreach ($addresses as $a) {
                    fputs($s, 'RCPT TO:'.$a."\r\n");
                    $l = fgets($s, 1024);
                    if (substr($l, 0, 3) != '250') {
                        Core::log('E', 'recipient error: '.trim($l), 'email');
                    }
                }
                //the message
                fputs($s, "DATA\r\n");
                $l = fgets($s, 1024);
                if (substr($l, 0, 3) != '250') {
                    Core::log('E', 'data error: '.trim($l), 'email');

                    return false;
                }
                fputs($header."\r\n".str_replace(array("\n.\n", "\n.\r"), array("\n..\n", "\n..\r"), $message)."\r\n.\r\n");
                $l = fgets($s, 1024);
                if (substr($l, 0, 3) != '250') {
                    Core::log('E', 'data send error: '.trim($l), 'email');

                    return false;
                }
                //say bye
                fputs($s, "QUIT\r\n");
                fclose($s);
            // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }

    /**
     * validate and shape an email address.
     *
     * @param email addres (in either "name <account@domain>", "account@domain", "name <account@[ip address]>" or "account@[ip address]" format
     * @param type header type to add address to
     */
    private function address($email, $type = 'To')
    {
        //! check if it's a valid email address
        if (preg_match("/(.*?)?[\<]?(([^\<]+)\@((\[?)[a-zA-Z0-9\-\.\:\_]+([a-zA-Z]+|[0-9]{1,3})(\]?)))[\>]?$/", $email, $m)) {
            //! only localhost allowed not to contain dot
            if (strpos($m[4], '.') === false && $m[4] != 'localhost') {
                throw new EmailException(L('Bad email address').': '.$email);
            }
            //! remove if it's already exists in headers to avoid duplications
            foreach (['To', 'Cc', 'Bcc'] as $rcpt) {
                if (!empty($this->header[$rcpt][$m[2]])) {
                    unset($this->header[$rcpt][$m[2]]);
                }
            }
            //! add to headers
            $this->header[$type][$m[2]] = '=?utf-8?Q?'.
                quoted_printable_encode(
                str_replace("\r", '',
                str_replace("\n", ' ',
                str_replace('@', ' AT ', !empty($m[1]) ? trim($m[1]) : $m[3])))).
                '?= <'.$m[2].'>';

            return true;
        }
        throw new EmailException(L('Bad email address').': '.$email);
    }

    public function cronMinute($item)
    {
        //! get real mailer backend ($core->mailer points to db queue backend)
        // @codeCoverageIgnoreStart
        if (empty(Core::$core->realmailer)) {
            Core::log('C', 'Real mailer backend not configured!');
        }
        // @codeCoverageIgnoreEnd

        //! get items from database
        $lastId = 0;
        while ($row = DS::fetch('*', 'email_queue', 'id>?', '', 'id ASC', [$lastId])) {
            $email = new self($row['data']);
            $lastId = $row['id'];
            try {
                if (!$email->send(Core::$core->realmailer)) {
                    // @codeCoverageIgnoreStart
                    throw new \Exception('send() returned false');
                }
                DS::exec('DELETE FROM email_queue WHERE id=?;', [$row['id']]);
            } catch (\Exception $e) {
                Core::log('E', 'Unable to send #'.$row['id'].' from queue: '.$e->getMessage());
            }
                    // @codeCoverageIgnoreEnd
            sleep(1);
        }
    }
}
