<?php

class ScriptDebugger{

	public static function compute_time(){

 		list($usec, $sec) = explode(" ", microtime());
 		return (float)$usec + (float)$sec;

	}

	public static function compute_script_exec_time(){

		$start = compute_time();

   		for ($i = 0; $i < 5; $i++) {
    			sleep(1);
   		}

		echo 'Script took: ' . (compute_time() - $start). ' seconds to execute';

	}
}

?>