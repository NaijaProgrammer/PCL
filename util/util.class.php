<?php

class Util
{
	public static function is_scalar($val)
	{
		return ( !is_object($val) && !is_array($val) );
	}

	/**
    *@credits: stackoverflow.com/questions/6800015/check-whether-the-function-needs-parameters
 	*@date: April 4, 2013 8:29pm
	*@author: Michael Orji
	**/
	/*public static function function_takes_arguments($function_name){

		$reflector = new ReflectionFunction($function_name);
		return $reflector->getNumberOfParameters();
	}*/
	
	/**
    *@credits: stackoverflow.com/questions/13847509/how-to-count-the-arguments-of-a-method-before-calling-that-method-which-is-inside
 	*@date: April 5, 2013 5:14am
	*@author: Michael Orji
	**/
	public static function function_takes_arguments($class_name, $function_name)
	{
		$reflector = new ReflectionMethod($class_name, $function_name);
		return $reflector->getNumberOfParameters();
	}
	
	/**
	*@author: Michael Orji
	*@date: April 5, 2013 5:14am
	**/
	public static function get_method_as_function($class_name, $method_name)
	{
		$reflector = new ReflectionClass($class_name);
		$ref_met   = $reflector->getMethod($method_name);
		return $ref_met->name;
	}
	
	public static function get_calling_method($level = 0)
	{
		$level = $level + 2; //[0] is this method, [1] is the method whose caller we are looking for
		$callers = debug_backtrace();
		$arr['caller_method'] = $callers[$level]['function'];
		$arr['caller_class']  = $callers[$level]['class'];
		return $arr;
	}
	
	/**
	* Generates a storable representation of a value
	* @author Michael Orji
	*/
	public static function stringify($data)
	{
		return base64_encode(serialize($data));
	}
   
	public static function unstringify($data)
	{
		return  unserialize(base64_decode($data));
	}
	public static function is_stringified($data)
	{
		//TO DO: implement
	}
	
	/**
	* @credits php manual: unserialize, first user contributed note
	*/
	public static function is_serialized($str)
	{
		$blSerialized=(@unserialize($str)||$str=='b:0;');
		return $blSerialized;
	}
}

?>