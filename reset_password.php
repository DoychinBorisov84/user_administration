<?php
session_start();

require_once 'customFunctions/db_config.php';
include 'classes/Database.class.php';

// New db-object
$db = new Database();

$pass = $_POST['resetpassword'];
$reset_string = $_POST['reset_password'];

// Select the user from the DB based on the reset_string passed as parameter in the form
$userForReset = $db->selectUserResetstring($reset_string);

if($userForReset){
	// var_dump($sql_res);
	$password_hashed = password_hash($pass, PASSWORD_DEFAULT);

	   // Update the user password
	   $user_updated = $db->updateUserPassword($password_hashed, $userForReset['id']);

	   if($user_updated){
	   	 header('Location: http://single-page-application.lan/index.php?password_changed=success#formLogin');
	   	 die('User updated');
	   }
	   else{
	   	die('Unable to reset the pasword at that time');
	   }		
}
else{
	header('Location: http://single-page-application.lan/index.php');
	die('User not found');
}