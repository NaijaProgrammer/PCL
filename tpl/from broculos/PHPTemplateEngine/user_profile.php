<?php

	include("template.class.php");
	
	/**
	 * Creates a new template for the user's profile.
	 * Fills it with mockup data just for testing.
	 */
	$profile = new Template("user_profile.tpl");
	$profile->set("username", "monk3y");
	$profile->set("photoURL", "photo.jpg");
	$profile->set("name", "Monkey man");
	$profile->set("age", "23");
	$profile->set("location", "Portugal");
	
	/**
	 * Loads our layout template, settings its title and content.
	 */
	$layout = new Template("layout.tpl");
	$layout->set("title", "User profile");
	$layout->set("content", $profile->output());
	
	/**
	 * Outputs the page with the user's profile.
	 */
	echo $layout->output();
	
?>