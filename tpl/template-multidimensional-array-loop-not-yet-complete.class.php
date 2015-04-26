<?php

	/**
	* Template engine class (use {tag} tags in your templates).
	* 
	* @link http://www.naijaprogrammer.com/ NaijaProgrammer Developer Tutorials
	* @author Michael Orji <michaelorji@naijaprogrammer.com>
	* @version 1.0
	*
	* @adapted from Simple template engine class by Nuno Freitas
	* @link http://www.broculos.net/
	* @author Nuno Freitas <nunofreitas@gmail.com>
	* @version 1.0
	*/
    class Template 
	{
    	/**
    	* The filename of the template to load.
    	*
    	* @access protected
    	* @var string
    	*/
        protected $file;
        
        /**
        * An array of values for replacing each tag on the template (the key for each value is its corresponding tag).
        *
        * @access protected
        * @var array
        */
        protected $values = array();
        
        /**
        * Creates a new Template object and sets its associated file.
        *
        * @param string $file the full path and filename of the template file to load
        */
        public function __construct($file)
		{
            $this->file = $file;
        }
        
        /**
        * Sets a value for replacing a specific tag.
        *
        * @param string $key the name of the tag to replace
        * @param string $value the value to replace
        */
        public function __set($key, $value)
		{
            $this->set($key, $value); //$this->values[$key] = $value;
        }
		
		/**
        * Sets a value for replacing a specific tag.
        *
        * @param string $key the name of the tag to replace
        * @param string $value the value to replace
        */
        public function set($key, $value)
		{
            $this->values[$key] = $value;
        }
        
        /**
        * Outputs the content of the template, replacing the keys for its respective values.
        *
        * @return string
        */
        public function output() 
		{
        	/**
        	* Tries to verify if the file exists.
        	* If it doesn't return with an error message.
        	* Otherwise load the file contents and loop through the array replacing every key with its value.
        	*/
            if (!file_exists($this->file)) 
			{
				$location = ''; //TO DO: get the location where the template file is being called
            	return "Error loading template file ($this->file) @ $location";
            }
			
            $output = file_get_contents($this->file);
			
            foreach ($this->values as $key => $value) 
			{
				/** 
				* don't add objects or arrays here, to avoid Array to String Conversion error
				* objects are handled by _do_loops() method
				*/
				if(is_string($value)) 
				{ 
					$tagToReplace = "{". $key. "}";
					$output = str_replace($tagToReplace, $value, $output);
				}
            }
			$output = $this->_do_loops( $output );
			$output = $this->_evaluate_conditions($output);
			
            return $output;
        }
		
		/**
		* Filter method _do_loop()
		* Evaluate and parse template loop condition(s)
		* @param string $output the template output to filter
		* @return string $output the filtered template
		* @date Oct. 22, 2014
		*/
		private function _do_loops( $output )
		{ 
			/**
			* pattern = 
			* containing the starting delimiter '{'
			* followed by zero or more space characters
			* followed by the keyword 'loop'
			* followed by zero or more space characters
			* followed by the colon punctuation ':'
			* followed by zero or more space characters
			* followed by one or more alphabets or underscore
			* followed by one or more alphabets, hyphen, underscore or numeral
			* followed by zero or more space characters
			* optionally followed by
			* (
			* one or more space characters
			* the keyword AS
			* one or more space characters
			* one or more alphabets or underscore
			* one or more alphabets, hyphen, underscore or numeral
			* zero or more space characters
			* )
			* followed by zero or more space characters
			* followed by ending delimiter '}'
			*
			* The optional part is used in a multidimensional array situation
			* 
			* The first parenthesized section helps us capture the string representing the first object to perform the loop on, using the $matches array.
			* The second parenthesized section helps us capture the string representing the second object, in a multidimensional array 
			*
			* This regex pattern will match: 
			* {loop:user_data}, { loop: user_data }, {loop : user-data1} one-dimensional arrays in the tpl file.
			* {loop:users AS user } multidimensional array in the tpl file
			* There must be at least one space before and after the 'AS' keyword otherwise, the entire 'AS' part and following
			* will be considered part of the outer array string and treated as a one-dimensional array
			* 
			* The parenthesized portion is then captured in $matches[1] and represents: user_data or user-data1
			* which are the place-holders for the object to perform the loop on
			*
			* the php script can then say Template-instance->user_data or Template-instance->user-data1 = object/array
			* e.g:
			* $tpl = new Template(/path/to/template/file);
			* $object = new StdClass();
			* $object->firstname = 'Michael';
			* $object->lastname  = 'Orji';
			* $tpl->user_data = array('firstname'=>'John', 'lastname'=>'Doe'); 
			* OR
			* $tpl->user_data = $object
			*
			* parenthesized are as follows:
			* $matches[0] = the complete pattern, e.g: {loop:user_data} or {loop:users AS user}
			* $matches[1] = the outer array or object: user_data or users
			* $matches[2] = the AS keyword and following part: AS user (exists only in a multidimensional array loop}
			* $matches[3] = the part following the AS keyword: user (exists only in a multidimensional loop}
			*/
			$loop_pattern = '/\{\s*loop\s*:\s*([a-zA-Z_]+[a-zA-Z-_1-9]+)\s*(\s+AS\s+([a-zA-Z_]+[a-zA-Z-_1-9]+))?\s*\}/';
			$loop_string  = preg_match($loop_pattern, $output, $matches); //e.g {loop : user_data}, {loop:users As user}
			
			/**
			* if there's no match, 
			* that is, the current template does not include a {loop:object-name} or a {loop:arrays AS array} condition, don't attempt to loop
			*/
			//if(empty($matches))
			if( !$loop_string )
			{
				return $output;
			}
			
			
			$outer_array_tpl_string = $matches[1]; //the string from the {loop:object-name} statement in the template that should be replaced with an object e.g: user_data, user-data1
			$inner_array_tpl_string = isset($matches[3]) ? $matches[3] : '';
			
			if( $outer_array_tpl_string && isset($this->values[$outer_array_tpl_string]) )
			{ 
			
				/**
				* extract the object/array value from the string representing the object to perform the loop on
				* for e.g in {loop:object-name}, Template-instance->user_data = object/array 
				* the key $outer_array_tpl_string = user_data, the value = object/array
				*/
				$outer_loop_object = $this->values[$outer_array_tpl_string]; 
				
		
				/**
				* if user passed a scalar value:
				* replace the {loop:object-name} or {loop:arrays As array} with the scalar value in the output
				* replace the supposed data members of the object with empty strings
				*/
				if( !is_object($outer_loop_object) && !is_array($outer_loop_object) )
				{
					/**
					* the pattern for the members of the object/array
					* there must be no space between the object, the object-member access operator (.) and the member name
					* e.g user_data.firstname
					*/
					$object_member_pattern = "{". $outer_array_tpl_string. ".". $key. "}"; 
					$output = preg_replace($loop_pattern, $outer_loop_object, $output);
					$output = str_replace($object_member_pattern, '', $output);
					
					return $output;
				}
			
				/**
				* otherwise, replace the {loop:object-name} or {loop:arrays As array} with an empty string, since it shouldn't display in the output
				*/
				$output = preg_replace($loop_pattern, '', $output);
				
					/**
					* loop through the loop object and replace the data members of the object with their real values
					*/
					
					foreach($outer_loop_object AS $key => $value)
					{
						/**
						* $object_member_pattern = the pattern for the members of the object/array
						* there must be no space between the object, the object-member access operator (.) and the member name
						* e.g {user_data.firstname}
						*
						* $object_member_value = the value of the object member
						* e.g user_data.firstname's value might be 'John'
						*/
						
						/**
						* if we are dealing with a multi-dimensional template loop { loop : users AS user }
						*/
						if( (is_array($value) || is_object($value)) && ($inner_array_tpl_string) )
						{
							foreach($value AS $inner_key => $scalar_value)
							{
								$object_member_value   = $scalar_value;
								$object_member_pattern = "{". $inner_array_tpl_string. ".". $inner_key. "}";
								$output = str_replace($object_member_pattern, $object_member_value, $output);
							}
						}
						
						/**
						* one-dimensional array
						*/
						else
						{    
							$object_member_value   = $value;
							$object_member_pattern = "{". $outer_array_tpl_string. ".". $key. "}"; 
							$output = str_replace($object_member_pattern, $object_member_value, $output); 
						}
					}
			}
			
			return $output;
		}
		
		/**
		* Filter method _evaluate_condition()
		* Evaluate and parse template if/else condition(s)
		* @param string $output the template output to filter
		* @return string $output the filtered template
		*/
		private function _evaluate_conditions($output)
		{
            /*$ifTagToReplace = "{@if-condition". $key. "}";
			$ifElseTagToReplace = "{@if-condition". $key. "}";
			$whileTagToReplace = "{@if-condition". $key. "}";
			$doWhileToReplace = "{@if-condition". $key. "}";
            $output = str_replace($ifTagToReplace, $value, $output);
			$output = str_replace($ifElseTagToReplace, $value, $output);
			$output = str_replace($whileTagToReplace, $value, $output);
			$output = str_replace($doWhileTagToReplace, $value, $output);*/
			return $output;
		} 
        
        /**
        * Merges the content from an array of templates and separates it with $separator.
        *
        * @param array $templates an array of Template objects to merge
        * @param string $separator the string that is used between each Template object
        * @return string
        */
        static public function merge($templates, $separator = "\n") 
		{
            $output = "";
            
			/**
        	* Loops through the array concatenating the outputs from each template, separating with $separator.
        	* If a type different from Template is found we provide an error message. 
        	*/
            foreach ($templates as $template)
			{
            	$content = (get_class($template) !== "Template") ? "Error, incorrect type - expected Template." : $template->output();
            	$output .= $content . $separator;
            }
            
            return $output;
        }
    }

?>