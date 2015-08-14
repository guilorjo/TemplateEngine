<?php
	define('ERROR_LOG_FILE', 'error.log');
	define('ERROR_LOG_ALL', true);

	require_once 'engine/Engine.class.php';
	require_once 'engine/EngineException.class.php';
	require_once 'config/global_vars.php';

	$page = (isset($_GET['page']))? htmlentities($_GET['page']) : HOME_PAGE;

	try{
		$e = new Engine($page);
		$e->display();
	} catch(EngineException $e){
	 	echo $e;
	 	exit();
	}
?>