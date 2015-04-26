<?php

class EmailValidator extends Validator
{

	/*
   	public static function valid_email($email)
	{
       	$valid_email_regex = "". "^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+".
        "@".
        "[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.".
        "[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$";

		return ereg($valid_email_regex, $email);

   	}*/

	public static function valid_email($email)
	{
		//credits: PHP 5: social Networking, chap 3. pg, 72
		return preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[az0-9-]+)*(\.[a-z]{2,4})^", $email );
	}

	public static function is_valid_email($email)
	{
		return self::valid_email($email);
	}
}

?>