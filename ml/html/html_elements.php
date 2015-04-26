<?php
//****************************************************************
//* 
//* Copyright 2006 Christoph Koehler, Mario Matzulla
//* 
//****************************************************************

// TODO: do we need the addElement() methods??
// define single, self-(en)closing tag and normal tag
// for use with sprintf
define('SETAG', '<%s %s />'."\n");
define('TAG', '<%1$s %2$s>'."\n".'%3$s</%1$s>'."\n");

// basic abstract form class
class HTMLElement {
	
	// class variables: all form fields have
	// attributes and a tag name (e.g. input, select)
	var $attributes;
	var $tag;

	/**
	 * @name formElement
	 * @abstract constructor of basic class for a single form element
	 * @param [none]
	 */
	function __construct(){
		
		// initialize attributes
		$this->attributes = array();
	}
	
	/*
	function setAttributes($attr){
		foreach($attr as $key => $value) {
			$this->attributes[$key]=$value;	
		}
	}
	
	function getAttribute($key){
		return $this->attributes[$key];
	}
	*/
	
	// basic render method. Needs to be overridden
	/*
	 * @name render
	 * @abstract basic render method. Needs to be overridden
	 * @param [none]
	 */
	function render(){
		return 'You need to override this method!';	
	}
	
}

// describes a self enclosed element, like <input />
// also abstract
class singlePartElement extends HTMLElement {
			
	/*
	 * @name singlePartElement
	 * @abstract constructor
	 * @param [none]
	 */
	function __construct() {
		
		// call parent's constructor
		parent::__construct();
	}
	
	// renders a basic single element tag. Used by most of the
	// input type fields since it's the same
	/*
	 * @name singlePartElement
	 * @abstract constructor
	 * @param [none]
	 */
	function render(){
		
		// implode attributes to inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// substitute tag and attributes in field blueprint defined at
		// the top; then return
		$output = sprintf(SETAG, $this->tag, $attr);
		return $output;
	}	
}

// multi part element, like <select>bla</select>
// also abstract
class multiPartElement extends HTMLElement {
	
	/**
	 * @name multiPartElement
	 * @abstract calls parent contstructor
	 * @param [none]
	 */
	function __construct(){
		
		// call parent's constructor
		parent::__construct();		
	}
	
	/*
	function addElement($element){
		$this->elements[] = $element;
		return count($this->elements) -1;
	}
	
	function removeElementById($id){
		array_splice($this->elements,$id,1);
	}
	
	function getElementById($id){
		return $this->elements[$id];
	}
	*/
}

// defines label
class label extends multiPartElement {
	
	var $label;
	
	/*
	 * @name label
	 * @abstract constructor, creates label properties
	 * @param string $for
	 * @param string $label
	 * @param array $attr e.g. array('id' => 'someId', 'onmouseover' => 'draw();');
	 */
	function __construct($for, $label, $attr) {
		
		// call parent's constructor
		parent::__construct();
		
		$this->tag = 'label';
		$this->label = $label;
		$attr['for'] = $for;
		$this->attributes = $attr;
	}
	
	/*
	 * @name render
	 * @abstract renders XHTML of label
	 * @param [none]
	 */
	function render() {
		
		// implode attributes to inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// substitute tag and attributes in field blueprint defined at
		// the top; then return
		$output = sprintf(TAG, $this->tag, $attr, $this->label);
		return $output;
	}
}

class container extends multiPartElement {
	var $components;
	
	function __construct() {
		parent::__construct();
		$this->components = array();
	}
	
	function add($component) {
		
		$this->components[] = $component;
	}
	
	function render() {
		die('Need to subclass render() method in container children');
	}
}

class fieldset extends container {
	
	var $legend;
	
	function __construct($legend, $attr) {
		parent::__construct();
		$this->legend = $legend;
		$this->tag = 'fieldset';
		$this->attributes = $attr;
	}
	

	
	function render() {

		// implode attributes to inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		$filling = sprintf(TAG,'legend', null, $this->legend);
		
		foreach ($this->components as $comp) {
			$filling .= $comp->render();
		}
		
		$output = sprintf(TAG, $this->tag, $attr, $filling);
		
		return $output;
	}
}

class html_elements_helper {

	/**
	 * @name implodeAttributes
	 * @abstract Takes an array and makes a space-separated list of parameters to include in the tag; $key = "$value".
	 * @param array() $params associative array that is imploded
	 */
	function implodeAttributes($attr) {

		// if null, make empty array
		if($attr == null) {
			$attr = array();
		}
		
		// initialize the returned value
		$implodedAttr = null;

		//loop through $params array and implode
		foreach($attr as $key => $value) {
			
			// add new key-value pair to returned variable
			$implodedAttr .= $key . '="' . $value . '" ';
		}
		
		// return the imploded value
		return $implodedAttr;
		
	}
	
	function debug($array) {	
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
}
?>