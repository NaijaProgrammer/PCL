<?php

//namespace PHPUtil\ml\html\forms\nibble;

require_once(PHPUTIL_DIR. 'util/array_manipulator.class.php');
require_once(PHPUTIL_DIR. 'ext/ml/html/forms/nibble_form/NibbleForm.class.php');
require_once(PHPUTIL_DIR. 'interfaces/serializable.php');

class NibbleFormExtended extends \NibbleForms\NibbleForm implements \PHPUtil\interfaces\Serializable{

	private $error_heading = '';
	
	/***************** BEGIN OVER-RIDING PARENT's FUNCTIONS *********************/
	
	/**
	* over-ridden to :
	* 1. enable us pass an array of parameters, thereby obviating the need to remember the exact order of the parameters
	* 2. enable form elements to be displayed in HTML div or span element containers
	*/
	public function __construct($form_attributes) {
	
		$default_attrs = array(
				'action'          => $_SERVER['PHP_SELF'],
				'submit_value'    => 'Submit',
				'html5'           => true,
				'method'          => 'post',
				'sticky'          => true,
				'message_type'    => 'list',
				'format'          => 'list',
				'multiple_errors' => false, 
				'error_heading'   => 'Sorry there were some errors in the form, problem fields have been highlighted'
			);
			
		$this->formats['div'] = array(
                'open_form'       => '<div>',
                'close_form'      => '</div>',
                'open_form_body'  => '',
                'close_form_body' => '',
                'open_field'      => '',
                'close_field'     => '',
                'open_html'       => "<div>\n",
                'close_html'      => "</div>\n",
                'open_submit'     => "<div>\n",
                'close_submit'    => "</div>\n"
            );
			
		$this->formats['span'] = array(
                'open_form'       => '<span>',
                'close_form'      => '</span>',
                'open_form_body'  => '',
                'close_form_body' => '',
                'open_field'      => '',
                'close_field'     => '',
                'open_html'       => "<span>\n",
                'close_html'      => "</span>\n",
                'open_submit'     => "<span>\n",
                'close_submit'    => "</span>\n"
        );
			
		$setup_attrs = ArrayManipulator::copy_array($default_attrs, $form_attributes); //\PHPUtil\util\ArrayManipulator::copy_array($default_attrs, $form_attributes);
			
		foreach($setup_attrs AS $key => $value){
			$$key = $value;
		}	
			
		$this->set_error_heading($error_heading);
			
		parent::__construct($action,$submit_value,$html5,$method,$sticky,$message_type,$format,$multiple_errors);
	
	}
	
	/**
	* over-ridden to enable us pass an array of parameters, 
	* thereby obviating the need to remember the exact order of the parameters
	*/
	public static function get_instance($form_attributes) {
	
		$default_attrs = array(
				'name'            => '',
				'action'          => $_SERVER['PHP_SELF'],
				'html5'           => true,
				'method'          => 'post',
				'submit_value'    => 'Submit',
				'format'          => 'list',
				'sticky'          => true,
				'message_type'    => 'list',
				'multiple_errors' => false, 
				'error_heading'   => 'Sorry there were some errors in the form, problem fields have been highlighted'
			);
			
		$setup_attrs = ArrayManipulator::copy_array($default_attrs, $form_attributes); //\PHPUtil\util\ArrayManipulator::copy_array($default_attrs, $form_attributes);
			
		foreach($setup_attrs AS $key => $value){
			$$key = $value;
		}
		
        if (!isset(self::$instance[$name])) {
            self::$instance[$name]
                = new NibbleFormExtended($form_attributes);
        }

        return self::$instance[$name];
    }
	
	/**
	* over-ridden to :
	* 1. enable us output any error message only if the form has been submitted, and not before the form has been submitted
	* 2. reset the error messages (see reset_error_messages() ) 
	*/
	public function render()
    {
        $fields = '';
        $error = ( (!$this->submitted() || $this->valid) ? '' : '<p class="error">'. $this->get_error_heading(). '</p>');
        $format = (object)$this->formats[$this->format];
        $this->setToken();

        foreach ($this->fields as $key => $value) {
            $format = (object)$this->formats[$this->format];
            $temp = isset($this->data[$key]) ? $value->returnField($this->name, $key, $this->data[$key])
                : $value->returnField($this->name, $key);
            $fields .= $format->open_field;
            if ($temp['label']) {
                $fields .= $format->open_html . $temp['label'] . $format->close_html;
            }
            if (isset($temp['messages'])) {
                foreach ($temp['messages'] as $message) {
                    if ($this->message_type == 'inline') {
                        $fields .= "$format->open_html <p class=\"error\">$message</p> $format->close_html";
                    } else {
                        $this->setMessages($message, $key);
                    }
                    if (!$this->multiple_errors) {
                        break;
                    }
                }
            }
            $fields .= $format->open_html . $temp['field'] . $format->close_html . $format->close_field;
        }

        if (!empty($this->messages)) {
            $this->buildMessages(); //after this call, the global messages array becomes a string
			$error_messages = $this->messages; //store the string in the error_messages variable, so that ...
			$this->reset_error_messages(); //we can reset the error_messages array, and be able to still output any error messages when the function returns below
        } else {
		    $error_messages = '';
            $this->messages = false;
        }
        self::$instance = false;
        $attributes = $this->getFormAttributes();
		
        
        return <<<FORM
            $error
			$error_messages
            <form class="form" action="$this->action" method="$this->method" {$attributes['enctype']} {$attributes['html5']}>
              $format->open_form
                $format->open_form_body
                  $fields
                $format->close_form_body
                $format->open_submit
                  <input type="submit" name="submit" value="$this->submit_value" />
                $format->close_submit
              $format->close_form
            </form>
FORM;
    }
	
	/************************* END OVER-RIDING PARENT's METHODS ************************/
	
	
	/*********************** SUB-CLASS-ONLY METHODS ******************************/
	
	public function get_fields(){
	
		return $this->fields;
	}
	
	/**
	* implements the serialize method of the Serializable interface which this class implements
	* enables proper serialization of objects of the class
	*/
	public function serialize(){
	
		return serialize($this);
	
	}
	
	/**
	* implements the unserialize method of the Serializable interface which this class implements
	* enables proper deserialization of the instances of this class
	*/
	public function unserialize($serialized_obj){
	
		unserialize($serialized_obj);
	
	}
	
	/**
	* sets custom error heading as opposed to the default 'Sorry there were some errors in the form, problem fields have been highlighted'
	* of the parent class
	*/
	public function set_error_heading($error_heading = ''){
		$this->error_heading = $error_heading;
	}
	
	/**
	* gets the custom error heading for the current object
	*/
	public function get_error_heading(){
		return $this->error_heading;
	}
	
	
	/*
	* If form is not posted to itself, but to another page, the error handling and error message display propagate poorly
	* this function resets error messages, so that they propagate properly across pages 
	* to see what happens without this function, uncomment the function body and post the form to another page for processing
	*/
	public function reset_error_messages(){
	
		/*convert the global messages variable back to an array, 
		* because after call to parent's builtMessages() method, it is turned into a string
		* which (potentially) leads to errors when we try to assign values to or manipulate the array using [] or any array method
		*/
		$this->messages = array();  
		
		/*
		* reset the (error) messages array of every field of the form
		*/
		foreach ($this->fields as $name => $field_object) {
		  
			foreach($field_object AS $key => $value){
			
			 //echo $name . ' ['. $key . '] = '. $value. '<br>';
				if($key == 'error'){
					$field_object->$key = array();
					unset($_SESSION[$key]);
				}
			}
		}
	}
	
	/**
	* auxiliary method, used by the render() method
	* determines if the form has been submitted
	*/
	protected function submitted(){
	
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	
	}
}

?>