<?php

/**
* PHP Social Networking
* @author Michael Peacock
* @modified Michael Orji
* Registry Class
*/
class Registry {

	/**
	* Array of objects
	*/
	protected $objects  = array();

	/**
	* Array of settings
	*/
	protected $settings  = array();
	protected $vars = array();

	/**
	* default constructor
	*/
	public function __construct() {}

	/**
 	* @set undefined vars
 	* @param string $index
 	* @param mixed $value
 	* @return void
 	*/
 
	public function __set($index, $value)
	{
        	$this->vars[$index] = $value;
 	}

	/**
 	* @get variables
 	* @param mixed $index
 	* @return mixed
 	*/
 	public function __get($index)
	{
        	return $this->vars[$index];
 	}

	/**
	* Create a new object and store it in the registry
	* @param String $object the object file prefix
	* @param String $key pair for the object
	* @return void
	*/
	public function createAndStoreObject( $object, $key )
	{
		$this->objects[ $key ] = new $object( $this );
	}

	/**
	* Store Setting
	* @param String $setting the setting data
	* @param String $key the key pair for the settings array
	* @return void
	*/
	public function storeSetting( $setting, $key )
	{
		$this->settings[ $key ] = $setting;
	}

	/**
	* Get a setting from the registries store
	* @param String $key the settings array key
	* @return String the setting data
	*/
	public function getSetting( $key )
	{
		return $this->settings[ $key ];
	}

	/**
	* Get an object from the registries store
	* @param String $key the objects array key
	* @return Object
	*/
	public function getObject( $key )
	{
		return $this->objects[ $key ];
	}
}

?>