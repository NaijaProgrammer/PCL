<?php

class AutoLoader
{
	private static $paths_and_exts = array();

	public static function load_class_on_demand($classes_path, $file_ext = '.class.php'){

		self::$paths_and_exts[$classes_path] = $file_ext;
		
		spl_autoload_register( 

			function($class_name) 
			{ 
				return AutoLoader::uppper_case_class_loader($class_name);
			} 
		);
	}


	public static function uppper_case_class_loader($class_name){

		$file_name       = strtolower($class_name[0]);
		$file_name_array = str_split( substr($class_name, 1) ); //don't include first letter since we already got it

		foreach($file_name_array AS $char){

			if(StringManipulator::is_upper_case($char)){ 

				$char = '_'. strtolower($char);
			}

			$file_name .= $char;
		}

		foreach(self::$paths_and_exts AS $current_path => $current_extension){

			$file = $current_path. $file_name. $current_extension;
        			 
        		if (file_exists($file)) {

            			require_once($file); 
            			return true;  
    			} 
		}

		return false; 
	}
}

?>