<?php


class Session {

	private static $_instance;

	private $_data_sessions;

	private function __construct(){
		$this->_data_sessions = array();

		if(($json = file_get_contents(SESSIONS_CONFIG,  FILE_USE_INCLUDE_PATH)) !== FALSE ){
			$j_conf = json_decode($json,TRUE);
			
			if(!empty($j_conf) && is_array($j_conf)){
				foreach($j_conf as $name => $config){
					session_name($name);
					session_start();
					session_regenerate_id();

					$data = array();
					$empty_session = FALSE;

					foreach($config['data'] as $param){
						if(isset($_SESSION[$param])){
							$data[$param] = $_SESSION[$param];
						} else {
							$empty_session = TRUE;
							break;
						}
					}
					if(!$empty_session){
						if(empty($_SESSION['ip'])){
							throw new EngineException("Le paramètre ip de SESSION doit être défini.");
						}
						if($_SESSION['ip'] != self::getIP()){
							$this->destroy($name);
							break;
						}
						$this->_data_sessions[$name] = $data;
					}			
					session_write_close();
				}
			}
		} else {
			throw new EngineException("Impossible d'ouvrir le fichier de configuration des sessions.");
		}
	}

	public static function create(){
		if(!isset(self::$_instance)) {
            self::$_instance = new self();  
        }
        return self::$_instance;
	}

	public function get($session_name){
		if($this->isset($session_name)){
			return $this->_data_sessions[$session_name];
		}
		return false;
	}

	public function isset_session($session_name){
		return isset($this->_data_sessions[$session_name]);
	}

	public function destroy($session_name){
		if(isset($this->_data_sessions[$session_name])){
			session_name($session_name);
			session_destroy();
			unset($_SESSION);
			unset($this->_data_sessions[$session_name]);
		} else {
			throw new EngineException("Impossible de detruire une session inexistante.");
		}
	}

	public static function getIP(){
		if (preg_match( "/^([d]{1,3}).([d]{1,3}).([d]{1,3}).([d]{1,3})$/", getenv('HTTP_X_FORWARDED_FOR'))){
	        $IParray=array_values(array_filter(explode(',',getenv('HTTP_X_FORWARDED_FOR'))));
	        return end($IParray);
	    }
	    return (empty(getenv('REMOTE_ADDR')))? null : getenv('REMOTE_ADDR');
	}
}