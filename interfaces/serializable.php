<?php
namespace PHPUtil\interfaces;

interface Serializable {

	function serialize();
	function unserialize($serialized_obj);

}

?>