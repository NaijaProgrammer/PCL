<?php

require_once('html_elements.php');

// basic abstract input class, extended by the different types
class formInput extends singlePartElement {
	
	// constructor
	function __construct(){
		
		// call parent's constructor
		parent::__construct();
		
		// define tag name
		$this->tag = 'input';
	}	
}

// select field
class formSelect extends multiPartElement {

	// holds the various <option> elements
	var $elements;
	
	// constructor, see class.tx_forms.php for more info on arguments
	function __construct($selectAttr, $optionsAttr, $selected, $options){
		
		// call parent's constructor
		parent::__construct();
		
		// define tag name
		$this->tag = "select";
		
		// add elements based on arguments given
		$this->addElements($options,$optionsAttr, $selected);
		
		// set attributes global
		$this->attributes = $selectAttr;
	}
	
	// adds <option> elements to select tag
	function addElements($options,$optionsAttr, $selected) {
		
		// loop through all the options
		foreach($options as $value => $text) {
			
			// make attributes unique from template given
			$optionsHere = $optionsAttr;
			
			// add value to array
			$optionsHere['value'] = $value;
			
			// if the current option tag is the selected, mark it so;
			// if multiple ones are selected, check array for those.
			if($selected !== null && $selected == $value) {
				$optionsHere['selected'] = 'selected';
			} else if(is_array($selected) && in_array($value, $selected)) {
				$optionsHere['selected'] = 'selected';
			}
			
			// build option tag			
			$this->elements[] = new selectOption($optionsHere, $text);
		}
	}
	
	// renders the select field
	function render() {
		
		// implode attributes to inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// initialize the content between the select tags
		// aka options 
		$filling = null;
		
		// loop through all the options and render them
		foreach($this->elements as $field) {
			$filling .= $field->render();
		}
	
		// make the tag and return it
		$output = sprintf(TAG, $this->tag, $attr, $filling);
		return $output;
	}
}

// defines an option tag
class selectOption extends multiPartElement {
	
	// holds text between tags
	var $text;

	// constructor
	function __construct($attr,$text){
		
		// call parent's constructor
		parent::__construct();
		
		// define tag name
		$this->tag = "option";
		
		// make text global
		$this->text= $text;
		
		// set attributes
		$this->attributes = $attr;
	}
	
	// renders the option tag
	function render() {
		
		// implode attributes for inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// make and return tag
		return sprintf(TAG, $this->tag, $attr, $this->text);
	}
}

// makes a submit button
class formSubmit extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global
		$this->attributes = $attr;
		
		// define type
		$this->attributes['type'] = 'submit';
	}
}

// makes a reset button
class formReset extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global
		$this->attributes = $attr;
		
		// define type
		$this->attributes['type'] = 'reset';
	}
}

// shows a input type text field
class formText extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global
		$this->attributes = $attr;
		
		// define type
		$this->attributes['type'] = 'text';
	}
}

// makes a textarea
class formTextarea extends multiPartElement {
	
	// variable to hold content between tags
	var $text;
	
	// constructor
	function __construct($attr, $text) {
		
		// call parent's constructor
		parent::__construct();
		
		// make text and attributes global
		$this->text = $text;
		$this->attributes = $attr;
		
		// define tag
		$this->tag = 'textarea';
	}
	
	// renders the form
	function render() {
		
		// implode attributes to inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// generate field and return it
		return sprintf(TAG, $this->tag, $attr, $this->text);
	}
}

// makes input type password
class formPassword extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global and define type
		$this->attributes = $attr;
		$this->attributes['type'] = 'password';
	}	
}

// makes input type hidden
class formHidden extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global and define type
		$this->attributes = $attr;
		$this->attributes['type'] = 'hidden';
	}
}

// make input type file
class formFile extends formInput {
	
	// constructor
	function __construct($attr) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global and define type		
		$this->attributes = $attr;
		$this->attributes['type'] = 'file';
	}
}

// makes a radio button
// TODO: make sure this is a good way to make the field
class formRadio extends formInput {
	
	// need extra vars: whether it's checked, 
	// and the different elements
	var $checked;
	var $elements;
	
	// constructor - see class.tx_forms.php for argument info
	function __construct($attr, $checked, $elements) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global and define type
		$this->checked = $checked;
		$this->attributes = $attr;
		$this->attributes['type'] = 'radio';
		$this->elements = $elements;
	}
	
	// renders radio buttons
	function render() {
		
		// declare output variable
		$radioTags = array();
		
		$tagNumber = 1;
		
		// loop through all options and render tags
		foreach($this->elements as $value) {		
			
			// make attributes unique for this tag
			$attrHere = $this->attributes;
			
			// change id for this radio button
			$attrHere['id'] = $attrHere['id'] . '-' . $tagNumber;

			// add value attribute
			$attrHere['value'] = $value[0];
			
			// if selected, determine that now
			if($value[0] == $this->checked) {
				$attrHere['checked'] = 'checked';	
			}
					
			// create label for this radio button
			$label = new label($attrHere['id'], $value[1], array('id' => $attrHere['id'] . '-label'));
			$label = $label->render();
			$tagNumber ++;
			
			
			// get attributes for inclusion
			$attrHere = tx_forms_helper::implodeAttributes($attrHere);
			
			// render tag
			$radioTags[] = $label . sprintf(SETAG, $this->tag, $attrHere);
		
		}
		
		// implode and return radio tags
		return implode('', $radioTags);
	}
}

// makes a checkbox
class formCheckbox extends formInput {
	
	// need to know whether or not it's checked
	var $checked;
	
	// constructor
	function __construct($attr, $checked) {
		
		// call parent's constructor
		parent::__construct();
		
		// make attributes global and define type
		$this->attributes = $attr;
		$this->attributes['type'] = 'checkbox';
		$this->checked = $checked;
	}
	
	// render checkbox
	function render() {

		// if it should be checked, do so now, but only if
		// it hasn't been defined in $attr
		if($this->checked && !isset($this->attributes['checked'])) $this->attributes['checked'] = 'checked';

		// implode attributes into inline html
		$attr = tx_forms_helper::implodeAttributes($this->attributes);
		
		// make field and return
		return sprintf(SETAG, $this->tag, $attr);	
	}
}

?>