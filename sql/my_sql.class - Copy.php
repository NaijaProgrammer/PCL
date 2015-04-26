<?php
/**
* Database management / access class: basic abstraction
*
* @author Michael Peacock
* @Modified: Michael Orji
* @version 1.0
*/
class MySql
{
   	/**
   	* Allows multiple database connections
   	* each connection is stored as an element in the array, and the
   	* active connection is maintained in a variable (see below)
   	*/
   	protected static $connections = array();

   	/**
   	* Tells the DB object which connection to use
   	* setActiveConnection($id) allows us to change this
   	*/
   	protected static $active_connection = 0;

   	/**
   	* Queries which have been executed and the results cached for
   	* later, primarily for use within the template engine
   	*/
   	//protected $query_cache = array();

   	/**
   	* Data which has been prepared and then cached for later usage,
   	* primarily within the template engine
   	*/
   	//protected $data_cache = array();

   	/**
   	* Number of queries made during execution process
   	*/
   	protected $query_counter = 0;

   	/**
   	* Record of the last query
	* @added by Michael Orji (?)
   	*/
   	protected $last_query_result_object;

	/**
	* id of the last insert query
	* @added by Michael Orji (?)
	*/
	protected $last_insert_id;
	
	/**
	* Queries which have been cached for execution later
	* @added by Michael Orji
	*/
	protected $cached_queries = array();
	
	/**
	* The currently executed/executing query
	* @added by Michael Orji
	*/
	protected $executed_query;
	
	/**
	* @added by Michael Orji
	* @date: Oct 14, 2013
	*/
	private $host;
	private $user;
	private $password;
	private $database;


	/**
    * Constructor
	* creates a new database connection if connection parameters are supplied
	* @param String database hostname
   	* @param String database username
   	* @param String database password
   	* @param String database we are using
   	* @return void [int the id of the new connection]
   	*/
	public function __construct( $host = '', $user = '', $password = '', $database = '' )
	{
		if( !empty($host) && !empty($user) && !empty($database) )
		{
			$this->connect($host, $user, $password, $database);
		}
	}

   	/**
   	* Create a new database connection
   	* @param String database hostname
   	* @param String database username
   	* @param String database password
   	* @param String database we are using
   	* @return int the id of the new connection
   	*/
   	public function connect( $host, $user, $password, $database )
	{
      		self::$connections[] = new mysqli( $host, $user, $password, $database );
      		$connection_id = count( self::$connections )-1;
      		if( mysqli_connect_errno() ) 
			{
         		trigger_error('Error connecting to host. '. self::$connections[$connection_id]->error, E_USER_ERROR);
      		}
			$this->host     = $host; 
			$this->user     = $user;
			$this->password = $password;
			$this->database = $database;
			$this->id       = $connection_id;
      		return $connection_id;
   	}
	
	/**
	* Sets the default character set to be used when sending data from and to the database server.
	* @param string the character set name
	* @return boolean true on success, false on failure
	* @author Michael Orji
	* @date Oct 20, 2014
	*/
	public function set_charset($charset)
	{
		return self::$connections[self::$active_connection]->set_charset($charset);
	}

   	/**
   	* Change which database connection is actively used for the next operation
   	* @param int the new connection id
   	* @return void
   	*/
   	public static function set_active_connection( $connection_id )
	{
    		self::$active_connection = $connection_id;
   	}
	
	/**
	* Get connection with supplied id
	* if ID is not supplied, return the currently active connection
	* @param $connection_id the id of the connection to get (optional)
	* @return object the currently active connection
	* @author Michael Orji
	* @date Oct. 20, 2014
	*/
	public static function get_connection( $connection_id = -1 )
	{
       	return $connection_id > -1 ? self::$connections[$connection_id] : self::$connections[self::$active_connection];
	}
	
	/**
	* return the just-executed query
	* @param void
	* @return String the executed query
	* @author Michael Orji
	* @date July 23, 2013
	*/
	public function get_executed_query()
	{
		return $this->executed_query;
	}
	
	/**
	* Cache a query string for later use
	* @param String the query key
	* @param String the query
	* @return void
	* @author: Michael Orji
	* @date: July 18, 2013
	*/
	public function cache_query($key, $query_str)
	{
		$this->cached_queries[$key] = $query_str;
	}
	
	/**
	* return cached query
	* @param  String the query key
	* @return String the cached query
	* @author: Michael Orji
	* @date: July 18, 2013
	*/
	public function get_cached_query($key)
	{
		return $this->cached_queries[$key];
	}

   	/**
   	* Execute a query string
   	* @param String the query
   	* @return void
   	*/
   	public function execute_query( $queryStr )
	{
		
      	if( !$result = self::$connections[self::$active_connection]->query( $queryStr ) )
		{
         	trigger_error('Error executing query: ' . $queryStr .' -'. self::$connections[self::$active_connection]->error, E_USER_ERROR);
			return false;
      	}
      	else
		{
			$this->executed_query = $queryStr;
         	$this->last_query_result_object = $result;
			return true;
      	}
   	}

   	/**
   	* Insert records into the database
   	* @param String the database table
   	* @param array data to insert field => value
   	* @return bool
   	*/
   	public function insert_records( $table, $data ){

      		$fields = "";
      		$values = "";

      		foreach ($data as $f => $v)
			{
				if( $this->column_exists_in_table($table, $f) )
				{
					$fields .= "`$f`,";
					$v = DataSanitizer::sanitize_data_for_db_query($v);
					$values .= ( is_numeric( $v ) && ( intval( $v ) == $v ) ) ? $v."," : "'$v',";
				}
      		}

      		// remove trailing ,
      		$fields = substr($fields, 0, -1);
      		$values = substr($values, 0, -1);

      		$insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
			
			echo $insert; exit;
			
      		$query_successfully_executed = $this->execute_query( $insert );
        
		if(!$query_successfully_executed)
		{
			return false;
		}
        $id_of_insert_query   = self::$connections[self::$active_connection]->insert_id;
		$this->last_insert_id = $id_of_insert_query;
		return ( ($id_of_insert_query > 0) ? $id_of_insert_query : $query_successfully_executed );// bcos, for a table without auto-increment field, mysqli->insert_id returns 0(zero)
   	}

	public function last_insert_id()
	{
		return $this->last_insert_id;
	}

   	/**
   	* Update records in the database
   	* @param String the table
   	* @param array of changes field => value
   	* @param String the condition
   	* @return bool
   	*/
   	public function update_records( $table, $changes, $conditions = '' ){

      		$update = "UPDATE " . $table . " SET ";

      		foreach( $changes as $field => $value )
			{
				if( $this->column_exists_in_table($table, $field) )
				{
					$update .= "`" . $field . "`='{$value}',";
				}
      		}

      		// remove  trailing ,
      		$update  = substr($update, 0, -1);
      		$update .= " WHERE true=true";

      		if( $conditions != '' )
			{
         		foreach($conditions AS $condition => $value)
				{
					if( $this->column_exists_in_table($table, $condition) )
					{
						$value = is_numeric($value) ? $value : "'$value'";
						$update .= " AND `$condition` = $value";
					}
         		}
      		}

      		$this->execute_query( $update );
			return $this->affected_rows();
   	}

   	/**
   	* Delete records from the database
   	* @param String the table to remove rows from
   	* @param Array key/value pair of the conditions for which rows are to be removed
   	* @param int the number of rows to be removed
   	* @return void
   	*/
   	public function delete_records( $table, $conditions, $limit = ''){

      		$limit        = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
      		$where_clause = "WHERE true=true";

      		foreach($conditions AS $condition => $value){

       			$where_clause .= " AND $condition = ( is_numeric($value) ? $value : '$value' )";

      		}

      		$delete = "DELETE FROM {$table} {$where_clause} {$limit}";
      		$this->execute_query( $delete );
   	}
	
   	/**
   	* Get the rows from the most recently executed query, excluding cached queries
   	* @return array
   	*/
   	public function get_rows($mode = MYSQLI_ASSOC)
	{
		$resource_object = $this->get_last_query_resource_object();
      	return is_object($resource_object) ? $resource_object->fetch_array($mode) : array();
   	}

   	public function num_rows()
	{
		$resource_object = $this->get_last_query_resource_object();
      	return is_object($resource_object) ? $resource_object->num_rows : 0;
   	}

   	/**
   	* Gets the number of affected rows from the previous query
   	* @return int the number of affected rows
   	*/
   	public function affected_rows()
	{
			
		$resource_object = $this->get_last_query_resource_object();
	
		/**
		* Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a MySQLi_Result object. For other * successful queries mysqli_query() will return TRUE.
		* source: php manual [mysqli::query]
		*/
      	return is_object($resource_object) ? $resource_object->affected_rows : $resource_object;
   	}
	
	/*
	* @date: April 11, 2013; 10:16 am
	* @author: michael orji
	*/
	public function get_last_query_resource_object()
	{
		return $this->last_query_result_object;
	}
	
	/**
	* @author: Michael Orji
	* @date: Oct 14, 2013
	*/
	public function column_exists_in_table($table, $column, $database = '')
	{
		$database = !empty($database) ? trim($database) : $this->database;
		$sql = "SELECT * FROM information_schema.COLUMNS ".
               "WHERE TABLE_SCHEMA = '". $database. "' ".
               "AND TABLE_NAME = '". $table. "' ". 
               "AND COLUMN_NAME = '". $column. "'";
		$this->execute_query($sql);
		return ( $this->num_rows() > 0 );
	}
	
	/**
	* @author: Michael Orji
	* @date: Nov 5, 2013
	*/
	public function get_table_columns($table, $database = '')
	{
		$database = !empty($database) ? trim($database) : $this->database;
		$sql = "SELECT column_name FROM information_schema.COLUMNS ".
               "WHERE TABLE_SCHEMA = '". $database. "' ".
               "AND TABLE_NAME = '". $table. "'";	   
		$this->execute_query($sql);
		return ArrayManipulator::reduce_redundant_matrix_to_array($this->return_result_as_matrix(), 'column_name');
	}

   	/**
   	* Close the passed connection
   	* close all of the database connections if no id is passed
   	* @param: connection_id (optional)
   	*/
   	public function disconnect($connection_id = -1)
	{
      	if($connection_id > -1)
		{
       		self::$connections[$connection_id]->close();
       		return;
      	}
      	foreach( self::$connections as $connection )
		{
         	$connection->close();
      	}
   	}
}
?>