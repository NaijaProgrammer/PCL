# pcl
PCL: The PHP Class Library
Provides functionality for performing common tasks.
It provides classes for:
Image Manipulation,
IO,
Multimedia
SQL
Template creation
Arrays,
String
and others...

Example Usage: 
<?php
//include the ini (bootstrap) file, 
require 'pcl/ini.php'; 

//proceed to call classes as needed

//determine paths to web application
$current_script_paths = UrlInspector::get_path(dirname(__FILE__));

define('SITE_DIR', $current_script_paths['dir_path']); //e.g /home/public_html/your_app_path

define('SITE_URL', rtrim($current_script_paths['http_path'], '/')); //e.g http://www.example-site.com
