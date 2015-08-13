<?php

date_default_timezone_set('Europe/Paris'); 

/**
 * Le moteur
 *
 * @author: Guillaume Marques <guillaume.marques33@gmail.com>
 * @author: Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 10/08/15
**/

require_once 'config/global_vars.php';
require_once 'Widget.class.php';
require_once 'View.class.php';
require_once 'EngineException.class.php';
require_once 'DataBase.class.php';
require_once 'Page.class.php';
require_once 'Pannel.class.php';
require_once 'InternLink.class.php';
;


/* Classe */
class Engine
{
	private $_name; //nom de la page
	private $_config;

	private $_widgets;
	private $_pannels;

	/**
	 * __contruct
	 * @param name, nom de la page (facultatif)
	**/
	function __construct($page = null){
		$this->_name = (!is_null($page))? $page : HOME_PAGE;

		if(($json = file_get_contents(PAGES_CONFIG,  FILE_USE_INCLUDE_PATH)) !== FALSE ){
			$j_conf = json_decode($json,TRUE);
			if(isset($j_conf[$this->_name])){
				$this->_config = $j_conf[$this->_name];
			} else {
				$this->_name = ERROR_404;
			}
		} else {
			throw new EngineException('Impossible de charger la configuration de la page');
		}

		$this->run();
	}	

	public function getName(){
		return $this->_name;
	}

	/**
	 * addWidget
	 * Ajout d'un widget dans la page. Ce widget remplacera la variable du template dont il porte le nom.
	 *
	 * @param Widget::w, widget
	**/
	public function addWidget($w){
		if($w instanceof Widget) {
			$this->_widgets[$w->getPosition()] = $w;
		} else {
			throw new EngineException('addWidget($w), le paramètre n\'est pas du type Widget.');
		}
	}


	public function addPannel($p){
		if($p instanceof Pannel) {
			$this->_pannels[$p->getPosition()] = $p;
		} else {
			throw new EngineException("addPannel($p), le paramètre n'est pas du type Pannel");
		}
	}
	

	/**
	 * Méthode assignToWidget
	 * @param $data = array()
	 *			Tableau permettant d'assigner des valeurs aux variables dont le nom se situe dans la clef
	 * @param $widget
	 *			Widget de destination de ces données
	**/
	public function assignToWidget($data=array(), $widget){
		$this->_widgets[$widget]->assign($data);
	}


	/**
	 * Méthode getParam
	 * Retourne le contenu d'une variable provenant du fichier de configuration
	 * La variable peut être en GET, en POST ou normal (default)
	**/
	private function getParam($method='default', $name=NULL){
		$method = strtolower($method);
		if(empty($name)){
			return NULL;
		}

		switch($method){
			case 'get':
				return isset($_GET[(string)$name]) ? htmlentities($_GET[(string)$name]) : NULL;

			case 'post':
				return isset($_POST[(string)$name]) ? htmlentities($_POST[(string)$name]) : NULL;

			default:
				return NULL;
		}
		// non atteint
		return NULL;
	}

	private function run(){
		//Ajout des widgets et des pannels
		$widgets = $this->_config['widgets'];
		$pannels = $this->_config['pannels'];

		if(!isset($widgets)){
			throw new EngineException("Configuration des widgets de la page incorrecte ");
		}

		if(!isset($pannels)){
			throw new EngineException("Configuration des pannels de la page incorrecte");
		}

		foreach($widgets as $pos => $name){
			$this->addWidget(new Widget($pos, $name));
		}

		foreach($pannels as $pos => $content){
			$pannel = new Pannel($pos);
			foreach($content as $w){
				$pannel->addWidget(new Widget($w['name'],$w['name']), $w['width']);
			}
			$this->addPannel($pannel);
		}
	}


	/**
	 * Méthode display
	 * Permet l'affichage de la page
	**/
	public function display()
	{
		//On commence par définir meta, styles et scripts
		$meta = Page::defineMetaElements($this->_config['meta']);
		$styles = Page::addCSSDocuments($this->_config['styles']);
		$scripts = Page::addJavascriptDocuments($this->_config['scripts']);

		$html = array();

		//On récupère l'affichage de tous les widgets
		if( count($this->_widgets) > 0){
			foreach($this->_widgets as $widget){
				$html[$widget->getPosition()]= $widget->getHTML();
			}
		}

		//On récupère l'affichage de tous les widgets
		if( count($this->_pannels) > 0){
			foreach($this->_pannels as $pannel){
				$html[$pannel->getPosition()]= $pannel->getHTML();
			}
		}
		extract($html); //Extraction de l'html 

		ob_start(); //Ouverture du tampon
		include TEMPLATE_PATH.$this->_config['template'].'.php'; //On insère le template
		$content = ob_get_contents();
		ob_end_clean(); //Fermeture + nettoyage tampon

		ob_start();
		include HTML_DEFAULT;
		$result = ob_get_contents();
		ob_end_clean();

		echo Page::minification($result); //On affiche le résultat
	}
}