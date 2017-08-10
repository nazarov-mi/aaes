<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/aaes/src/AssetsManager.php';

AssetsManager::init();

new App([
	'display_errors'	=> true,
	
	'host'				=> 'localhost',
	'name'				=> 'amakids2_db',
	'username'			=> 'root',
	'password'			=> '',

	// ...
]);