 <?php

/**
 * Classe Widget
 * Permet de creer un widget dans la page
 *
 * @author Guillaume Marques <guillaume.marques33@gmail.com>
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 10/08/2015
 **/

date_default_timezone_set('Europe/Paris');

 
require_once 'config/global_vars.php';
require_once 'Cache.class.php';
require_once 'InternLink.class.php';
require_once 'DataBase.class.php';
require_once 'inc/fct.inc.php';

class Widget
{
	protected $_position; //Place du widget
	protected $_name; //Nom du widget
	protected $_cache; //Cache du widget
	protected $_content;//Contenu du widget

	static protected $_config;
	protected $_my_config;

	/**
	 * __construct
	 *
	 * @param String::name, nom du widget
	 * @param String::var, variable qui contiendra ce widget
	**/
	function __construct($position, $name = null){
		$this->_position = $position;
		$this->_name = (is_null($name))? $position : $name;
		$this->_content = array();

		if(!isset(self::$_config)){
			if( (self::$_config = file_get_contents(WIDGETS_CONFIG, FILE_USE_INCLUDE_PATH)) === FALSE ){
				throw new EngineException("Impossible d'ouvrir le fichier de configuration des widgets.");
			}
			self::$_config = json_decode(self::$_config,TRUE);
		}

		if(is_string($this->_name) && isset(self::$_config[$this->_name])){
			$this->_my_config = self::$_config[$this->_name];
		} else {
			throw new EngineException("Erreur ou pas de configuration définie pour le widget ");
		}

		if($this->_my_config['cache_duration'] >60){
			$this->_cache = new Cache($name, $this->_my_config['cache_duration']);
		}

		if(!empty($this->_my_config['parameters']['vars'])){
			$this->assign($this->_my_config['parameters']['vars']);
		}
	}

	/**
	 * getName
	 * Retroune le nom du widget
	**/
	public function getName()
	{
		return $this->_name;
	}

	public function getPosition(){
		return $this->_position;
	}

	/**
	 * getContent
	 * Retourne les variables contenues dans le widget
	**/
	public function getContent()
	{
		return $this->_content;
	}


	/**
	 * assign
	 * Permet de donnnées des valeurs aux variables contenues dans le widget
	 *
	 * @param Array( nomVariable=>valeur ) data
	 **/
	public function assign( $data = array() )
	{
		foreach( $data as $k=>$v )
		{
			$this->_content[$k]=$v;

			if(isset($this->_cache)) //MAJ nom du cache
				$this->_cache->params($k, $v);
		}
	}


	/**
	 * getHTML
	 *
	 * @return code HTML du widget
	**/
	public function getHTML()
	{
		//Si le fichier est déjà enregistré on l'affiche
		if(isset($this->_cache) && !$this->_cache->isCacheExpired())
			return $this->_cache->getCacheContent();

		//Sinon
		return $this->getContentForHTTP();
	}

	/**
	 * getContentForHTTP()
	 *
	 * @return code HTML du widget pour une reqûete HTTP
	**/
	public function getContentForHTTP()
	{
		extract($this->_content);

		ob_start(); //On démarre le cache
		echo '<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->';
		if($this->_my_config['view']){
			include VIEW_PATH.$this->_name.VIEW_EXT;
		} else {
			include WIDGET_PATH.$this->_name.'/'.MODEL_FILE;
			include WIDGET_PATH.$this->_name.'/'.VIEW_FILE;
		}
		$result = ob_get_contents();
		ob_end_clean();

		if(isset($this->_cache)){
			if($this->_cache->updateCacheContent($result) === FALSE){
				throw new EngineException("Impossible de mettre à jour le cache.");
			}
		}

		return $result;
	}
}