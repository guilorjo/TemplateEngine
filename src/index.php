<?php
	define('ERROR_LOG_FILE', 'error.log');
	define('ERROR_LOG_ALL', true);

	session_start();

	require_once 'engine/Engine.class.php';
	require_once 'engine/EngineException.class.php';

	try{
		$e = new Engine($_GET['page']);
		$e->display();
	} catch(EngineException $e){
	 	echo $e;
	 	exit();
	}
?>