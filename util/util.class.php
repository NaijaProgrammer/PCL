<?php
class Util
{
	public static function is_scalar($val)
	{
		return ( !is_object($val) && !is_array($val) );
	}

	/**
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
		$level = $level + 2; //[0] is this method, i.e get_calling_method, [1] is the method whose caller we are looking for
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
	
	/*
	* @implemented_date May 16, 2015 8:46 am
	*/
	public static function is_stringified($data)
	{
		$decoded_data = base64_decode($data);
		return self::is_serialized($decoded_data);
	}
	
	public static function is_serialized($str)
	{
		$blSerialized=(@unserialize($str)||$str=='b:0;');
		return $blSerialized;
	}
}


private static function _get_items( $conditions = array(), $orders = array(), $limit = '' )
	{
		$item_table      = ITEM_MANAGER_TABLES_PREFIX. "items";
		$item_meta_table = ITEM_MANAGER_TABLES_PREFIX. "item_meta";
		
		if(!is_array($conditions) || empty($conditions) )
		{
			$sql = "SELECT {$item_table}.id, {$item_table}.name FROM $item_table, $item_meta_table ". 
			"WHERE true = true";
			
			if(!empty($orders) && is_array($orders))
			{ 
				$order_by_clause = " ORDER BY";
				foreach($orders AS $key => $value)
				{
					$order_by_clause .= " `$key` $value,";
				}
				// remove trailing ,
				$order_by_clause = substr($order_by_clause, 0, -1);
				$sql .= $order_by_clause;
			}
			
			$db_obj = self::get_db_object();
			$db_obj->execute_query($sql);
			return $db_obj->return_result_as_matrix();
		}
		
		else
		{
			$matrix = array();
			$counter = 0;
			
			$first_condition_key   = array_shift(array_keys($conditions));
			$first_condition_value = $conditions[$first_condition_key];
			
			$ids_sql = "SELECT item_id FROM ". $item_meta_table. " WHERE ";
			
			$ids_sql .= "({$item_meta_table}. meta_key  = '$first_condition_key' ";
			$ids_sql .= " AND {$item_meta_table}. meta_value = '". Util::stringify($first_condition_value). "')";
			
			foreach($conditions AS $condition => $value)
			{
				if($condition != $first_condition_key)
				{
					$value = DataSanitizer::sanitize_data_for_db_query($value);
					$value = is_numeric($value) ? intval($value) : $value; 
					$value = Util::stringify($value);
					$ids_sql  .= " AND {$item_meta_table}.meta_key  = '${condition}' ";
					$ids_sql  .= " AND {$item_meta_table}.meta_value = '$value'";
					++$counter;
				}
         	}
			
			if($counter > 0)
			{
				$ids_sql .= " GROUP BY `item_id` having count(*) = $counter";
			}
			
			if(!empty($limit))
			{
				$ids_sql .= " LIMIT ". $limit;
			}
			
			$db_obj = self::get_db_object();
			$db_obj->execute_query($ids_sql);
			$ids = $db_obj->return_result_as_matrix();
			$ids = ArrayManipulator::reduce_redundant_matrix_to_array($ids, 'item_id');
			
			for($i = 0; $i < count($ids); $i++)
			{
				$current_id = $ids[$i];
				$name_arr   = self::get_item_data($current_id, 'name');
				
				$matrix[$i]['id']   = $current_id;
				$matrix[$i]['name'] = $name_arr['name'];
				$matrix[$i] = self::get_item_data($current_id);
			}
			
			$matrix['num_of_rows'] = count($matrix);
			$matrix['sql_query_string'] = $ids_sql;
			
			return $matrix;
		}
	}
