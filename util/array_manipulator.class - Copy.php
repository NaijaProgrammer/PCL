<?php

//namespace PHPUtil\util;

class ArrayManipulator{

	/* 
	* A function that loops through the [N][named_element] element 
	* of a matrix to determine if a given vaulue ($search_value) exitsts
	*
	* @author: Michael Orji
	* @date: June 6, 2010
	* 
	* boolean in_matrix(mixed search_value, matrix matrix, string named_element);
	*
	*******************************************************************************/

	public static function in_matrix($search_value, $matrix, $named_element){

 		$matrix_length = count($matrix);

   		/* check to make sure we're not dealing with an empty matrix */
   		if($matrix_length > 0){

      			for($i = 0; $i < $matrix_length; $i++){

         			if(trim($matrix[$i][$named_element]) == trim($search_value)){
          				return true;
         			}
      			}
   		}

		return false;
	} 


	/**
	* copy second array into first array
	* @return array the new array
	* @author: Michael Orji
	* @date: 24 Dec., 2012
	*/
	public static function copy_array(&$default_array, $opts_array, $elements_to_ignore = array())
	{
		foreach($opts_array AS $key => $value)
		{
			$value = is_string($value) ? trim($value) : $value;

			if(!in_array($key, $elements_to_ignore, true))
			{
				if(is_array($default_array))
				{
					$default_array[$key] = $value;
				}
				else if(is_object($default_array))
				{
					$default_array->$key = $value;
				}
			}
		}
		return $default_array;
	}
	
	/**
	* converts a redundant matrix (i.e a matrix where each member is an array with just one key which is same key as the others) to array:
	* e. g $names = array( 0=>array('name'=>'mike'), 1=>array('name'=>'john'), 2=>array('name'=>'jude'), ... ), each member is an array with same key of 'name'
	* so, we avoid the redundancy by reducing the matrix to a single array thus: array(0=>'mike', 1=>'john', 2=>'jude') by calling:
	* ArrayManipulator::reduce_redundant_matrix_to_array( $names, 'name');
	* @return array the new (reduced) array
	* @author: Michael Orji
	* @date: Nov. 5, 2013
	*/
	public static function  reduce_redundant_matrix_to_array($matrix, $redundant_key)
	{
		$arr = array();
		foreach($matrix AS $arr_value)
		{
			$arr[] = $arr_value[$redundant_key];
		}
		return $arr;
	}
} 

?>