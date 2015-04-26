<?php
require_once('site_config.php');
require_once(BASE_DIRECTORY. 'modules/user_manager/user_manager.php');

/*it always inherits the index's registration form with it's error, while this one continues to post to itself
*/
$registration_form = UserManagerViewsLoader::get_form('registration_form');
if($registration_form->validate()){ echo 'success'; }
echo $registration_form->render();








?>