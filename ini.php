<?php 
require_once('util/url_inspector.class.php');
require_once('util/directory_inspector.class.php');
require_once('util/string_manipulator.class.php');

$current_script_paths = UrlInspector::get_path(dirname($_SERVER['SCRIPT_FILENAME'])); //get paths to currently executing script

defined("PHPUTIL_DIR") or define("PHPUTIL_DIR", str_replace( '\\', '/', dirname(__FILE__) ). '/');  //file path to PHPUtil
defined("CURRENT_SCRIPT_FILE_PATH") or define("CURRENT_SCRIPT_FILE_PATH", substr($current_script_paths['dir_path'], 0, -1)); //file system (directory) path to currently executing script
defined("CURRENT_SCRIPT_HTTP_PAHT") or define("CURRENT_SCRIPT_HTTP_PATH", substr($current_script_paths['http_path'], 0, -1)); //http path to currently executing script (available only if currently executing script is located inside the web root folder or any of its directories)

spl_autoload_register( 

function($class_name) { 

	
	$directories = DirectoryInspector::get_directory_contents(PHPUTIL_DIR, 'DIRECTORIES_ONLY', true);

	foreach($directories AS $directory){

		if( ($directory != '.') && ($directory != '..') && ($directory !== PHPUTIL_DIR. 'docs') ){

			$file_name       = strtolower($class_name[0]);
			$file_name_array = str_split( substr($class_name, 1) ); //don't include first letter since we already got it

			foreach($file_name_array AS $char){

				if(StringManipulator::is_upper_case($char)){ 

					$char = '_'. strtolower($char);
				}

				$file_name .= $char;
			}

			$file = PHPUTIL_DIR. $directory. '/'. $file_name. '.class.php';
        			 
        		if (file_exists($file)) { 

            			require_once($file); 
            			return true;  
    			} 

		}
	}

	return false; 
} );

?>