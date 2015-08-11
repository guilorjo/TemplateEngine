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
require_once 'inc/fct.inc.php';

class Widget
{
	protected $_position; //Place du widget
	protected $_name; //Nom du widget
	protected $_cache; //Cache du widget
	protected $_content = array(); //Contenu du widget

	/**
	 * __construct
	 *
	 * @param String::name, nom du widget
	 * @param String::var, variable qui contiendra ce widget
	**/
	function __construct($position, $name, $cacheDuration=0)
	{
		$this->_position = $position;
		$this->_name= $name;

		if($cacheDuration>60)
			$this->_cache = new Cache($name, $cacheDuration);
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
	 * getWidget
	 * Retourne le tableau des widgets inclus sous la forme :
	 * name => cacheDuration => 3600
	 *      => content => array getContent()
	 *      => widget => arrau getWidget()
	**/
	public function getWidget()
	{
		$array = array();
		foreach ($this->_widget as $k => $v)
		{
			$array[$k]['cacheDuration'] = $v->getCacheDuration();
			$array[$k]['content'] = $v->getContent();
			$array[$k]['widget'] = $v->getWidget();
		}
		return $array;
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
		include WIDGET_PATH.'/'.$this->_name.'/'.MODEL_FILE;
		include WIDGET_PATH.'/'.$this->_name.'/'.VIEW_FILE;

		$result = ob_get_contents();

		ob_end_clean();

		if(isset($this->_cache))
			$this->_cache->updateCacheContent($result);

		return $result;
	}
}