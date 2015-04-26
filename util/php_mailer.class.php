<?php

defined('NL') or define('NL', "\r\n");

class PhpMailer
{
	private $to;
	private $subject;
	private $message;
	private $headers;
  
	public function __construct($to, $subject, $message)
	{
		$this->to      = $to;
		$this->subject = $subject;
		$this->message = $message;
		$this->headers = '';
	}
   public function set_headers($options = array())
   {
		$mime_ver = isset($options['mime_version']) ? trim($options['mime_version']) : '1.0';
		$con_type = isset($options['content-type']) ? trim($options['content-type']) : ''; 
		$from     = isset($options['from'])         ? trim($options['from'])         : '';
		$reply_to = isset($options['reply_to'])     ? trim($options['reply_to'])     : '';
		$to       = isset($options['to'])           ? trim($options['to'])           : ''; 
		$cc       = isset($options['cc'])           ? trim($options['cc'])           : '';
		$bcc      = isset($options['bcc'])          ? trim($options['bcc'])          : '';

		$headers  = '';
		$headers .= $mime_ver ? 'MIME-Version: ' . $mime_ver. NL : '';
		$headers .= $con_type ? 'Content-type: ' . $con_type. NL : '';
		$headers .= $to       ? 'To: '           . $to.       NL : '';
		$headers .= $from     ? 'From: '         . $from.     NL : '';
		$headers .= $reply_to ? 'Reply-To: '     . $reply_to. NL : '';
		$headers .= $cc       ? 'Cc: '           . $cc.       NL : '';
		$headers .= $bcc      ? 'Bcc: '          . $bcc.      NL : '';
		$headers .= 'X-Mailer: PHP/' . phpversion(). NL;
		$this->headers .= $headers; 
	}
	public function set_header($header, $header_value)
	{
		$this->headers .= $header . ': '. $header_value. NL;
	}
	public function get_headers()
	{
		return $this->headers;
	}
	public function send()
	{
		/*****
		(Windows only) When PHP is talking to a SMTP server directly, 
		if a full stop is found on the start of a line, it is removed. 
		To counter-act this, replace these occurrences with a double dot.
		[SOURCE: PHP MANUAL]
		*****/
		$message = str_replace("\n.", "\n..", $this->message);

		/* 
		In case any of our lines are larger than 70 characters, we should use wordwrap()
		SOURCE: DITTO
		*/
		$message = wordwrap($message, 70);
		ini_set('sendmail_from', '127.0.0.1');
		return mail($this->to, $this->subject, $message, $this->get_headers());
	}
}

?>