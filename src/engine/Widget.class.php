 <?php

/**
 * Classe Widget
 * Permet de creer un widget dans la page
 *
 * @author Guillaume Marques <guillaume.marques33@gmail.com>
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 03/07/2013
 **/

date_default_timezone_set('Europe/Paris');

 
define("__WIDGETPATH__", 'widgets');
define("__MODELFILE__", 'model.php');
define("__VIEWFILE__", 'view.php');


require_once 'Cache.class.php';
require_once 'inc/fct.inc.php';

class Widget
{
	protected $_name; //Nom du widget
	protected $_var; // Variable qui contiendra le contenu html du widget

	protected $_cacheActivated; //booléen pour l'activation du cache
	protected $_cache; //Cache du widget
	protected $_cacheDuration; //Durée du cache du widget

	protected $_isAjax; //booléen pour affichage du widget en ajax

	protected $_content = array(); //Contenu du widget
	protected $_widget = array(); //Widgets contenu dans le widget /INCEPTION/

	protected $_error404 = FALSE; //Widgets contenu dans le widget /INCEPTION/

	/**
	 * __construct
	 *
	 * @param String::name, nom du widget
	 * @param String::var, variable qui contiendra ce widget
	**/
	function __construct($name, $cacheDuration=0, $var='', $isAjax = 'FALSE')
	{
		$this->_name= $name;
		$this->_var=$var;
		$this->_cacheDuration=$cacheDuration;
		$this->_cacheActivated = ($cacheDuration>60);

		$this->setIsAjax($isAjax);

		
		if($this->_cacheActivated)
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

	/**
	 * setIsAjax
	 * Retourne si le widget doit être affiché en ajax ou non
	 * @param String::name, nom du widget
	**/
	public function setIsAjax($isAjax = 'FALSE')
	{
		$this->_isAjax = strtoupper($isAjax);
		if($this->_isAjax == 'TRUE')
		{
			// widget en session pour le retrouver avec ajaxEngine
			$_SESSION['widgetsAjax'][$this->_name]['cacheDuration'] = $this->_cacheDuration;
			$_SESSION['widgetsAjax'][$this->_name]['content'] = $this->_content;
			$_SESSION['widgetsAjax'][$this->_name]['widget'] = $this->getWidget();
		}
	}

	/**
	 * getIsAjax
	 * Retourne si le widget doit être affiché en ajax ou non
	**/
	public function getIsAjax()
	{
		return $this->_isAjax;
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
	 * isError404
	 * Retourne si une erreur 404 est detectee
	**/
	public function isError404()
	{
		return $this->_error404;
	}

	/**
	 * getCacheDuration
	 * Retourne la durée de mise en cache du widget
	**/
	public function getCacheDuration()
	{
		return $this->_cacheDuration;
	}

	/**
	 * getCacheActivated
	 * Retourne si le widget est mis en cache
	**/
	public function getCacheActivated()
	{
		return $this->_cacheActivated;
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
			if($v instanceof Widget)
			{
				if($this->_cacheDuration > $v->getCacheDuration() && $v->getCacheDuration() > 60)
					$v->setIsAjax('TRUE');
				$this->_widget[$v->getVar()]=$v;
				if($this->_isAjax == 'TRUE')
					$_SESSION['widgetsAjax'][$this->_name]['widget'] = $this->getWidget();
			}
			else
			{
				$this->_content[$k]=$v;

				if($this->_cacheActivated) //MAJ nom du cache
					$this->_cache->params($k, $v);

				if($this->_isAjax == 'TRUE')
					$_SESSION['widgetsAjax'][$this->_name]['content'] = $this->_content;
			}
		}
	}

	/**
	 *
	 *
	 **/
	public function getVar()
	{
		return $this->_var;
	}

	/**
	 * getHTML
	 *
	 * @return code HTML du widget
	**/
	public function getHTML()
	{

		if($this->_isAjax == 'TRUE'){
			return $this->getContentForAjax();
		}

		//Si le fichier est déjà enregistré on l'affiche
		if($this->_cacheActivated && !$this->_cache->isCacheExpired())
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

		//On récupère l'affichage de tous les widgets inclus
		if( count($this->_widget) > 0)
		{
			foreach($this->_widget as $widget)
				$html[$widget->getVar()]=$widget->getHTML();
			extract($html); //Extraction de l'html 
		}

		extract($this->_content);

		ob_start(); //On démarre la cache

		echo '<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->';

		include __WIDGETPATH__.'/'.$this->_name.'/'.__MODELFILE__;
		if(empty($error_404))
			include __WIDGETPATH__.'/'.$this->_name.'/'.__VIEWFILE__;
		else
			$this->_error404 = TRUE;

		$result = ob_get_contents();

		ob_end_clean();

		if(!empty($this->_error404))
		{
			return NULL;
		}

		if($this->_cacheActivated)
			$this->_cache->updateCacheContent($result);

		return $result;
	}

	/**
	* getContentForAjax
	*
	* @return code HTML et javascript pour un futur appel en Ajax
	**/
	public function getContentForAjax()
	{
		$content = '<div class="widgetAjax_'.$this->_name.'"></div>';
		return $content;
	}
	
}