<?php

class Validator
{
	public static function validate($matrix){

 		$matrix_len   = count($matrix);
 		$err['error'] = false;

   		for($i = 0; $i < $matrix_len; $i++){ 
  
      			if($matrix[$i]['error_condition'])
				{
      				$err['error']          = true;
       				$err['status_message'] = $matrix[$i]['error_message'];
       				$err['error_message']  = $matrix[$i]['error_message'];
					
       				return $err;
      			}   
   		}

 		return $err;
	}

	public static function valid_form_token($posted_form_token, $sess_form_token){

  		/*
   		*prevent third party form submission
   		*/
   		if(!isset($posted_form_token, $sess_form_token)){   
    			return false;
   		}

   		/*
   		*prevent form (re)submission on page reload
   		*/
   		else if($posted_form_token != $sess_form_token){
    			return false;
   		}

		return true;

	}

	public static function validate_post($sessn_form_token, $posted_form_token){
 
   		if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
    			UrlManipulator::redirect(UrlInspector::get_referrer_page(), 302, 5, "Unauthorized Access!!! You are being redirected...");
   		}
   		if(!self::valid_form_token($posted_form_token, $sessn_form_token)){
    			UrlManipulator::redirect(UrlInspector::get_referrer_page(), 302, 5, "Invalid Form Post!!! Redirecting shortly...");
   		}
	}
}

?>