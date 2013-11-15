<?php

date_default_timezone_set('Europe/Paris'); 


/**
 * Le moteur
 *
 * @author: Guillaume Marques <guillaume.marques33@gmail.com>
 * @author: Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 03/07/13
**/

require_once 'Widget.class.php';
require_once 'CompositeWidget.class.php';
require_once 'HtmlText.class.php';
require_once 'View.class.php';
require_once 'EngineException.class.php';


/* Configuration */
define( "__WEBROOT__", $_SERVER['DOCUMENT_ROOT']);
define( "__CONTENT__", 'content'); //Pour acceder plus rapidement à la variable affichant le contenu dans le template ($content ici)
define( "__VIEWPATH__", 'views/');
define( "__CONFIGFILE__", 'engine/stats.engine.config.xml');




/* Classe */
class TemplateEngine
{
	static public $xmlConfig;
	private $_name; //nom de la page
	private $_template; //nom du template utilisé
	private $_widget = array(); //Widgets contenus dans la page
	private $_meta = array(); //Contenu des balises <meta> de notre

	private $_lang; //langue

	/**
	 * __contruct
	 * @param name, nom de la page (facultatif)
	**/
	function __construct()
	{
		if(func_num_args()==0)
		{
			$file = $_SERVER['PHP_SELF'];
			$file = str_replace('.php','',  $_SERVER['PHP_SELF']);
			$name = explode( '/', $file);
			$file= $name[count($name)-1];
		}
		else $file= func_get_arg(0);

		$this->_name = $file;
		$this->_lang = 'en'; //récupération de la langue dans l'url


		//Récupération de la configuration de la page
		foreach(TemplateEngine::$xmlConfig->pages->page as  $page)
			if( $this->_name == $page['name'])
			{
    			$this->_template= $page->template;
				$this->_meta['description']= $page->description;
				$this->_meta['title']= $page->title;
				$this->_meta['page_name']= $page['name'];
			}

		// indispensable pour cleaner ajax
		unset($_SESSION['widgetsAjax']);

		$this->baseWidget();
	}


	/**
	 * addCompositeWidget
	 * Ajout d'un compositeWidget dans la page
	 *
	 * @param String::name, nom du CompositeWidget ( cf variables du template)
	 * @param CompositeWidget::cw, le CompositeWidget
	**/
	public function addCompositeWidget($name, $cw)
	{
		try
		{
			if($cw instanceof CompositeWidget)
				$this->_widget[$name]= $cw;
			else
				throw new EngineException('addCompositeWidget, le deuxième paramètre n\'est pas du type CompositeWidget');
		}
		catch( EngineException $e)
		{
			echo $e;
			exit();
		}

	}
	

	/**
	 * addWidget
	 * Ajout d'un widget dans la page. Ce widget remplacera la variable du template dont il porte le nom.
	 *
	 * @param Widget::w, widget
	**/
	public function addWidget($w)
	{
		try
		{
			if($w instanceof Widget)
				$this->_widget[$w->getVar()]= $w;
			else
				throw new EngineException('addWidget($w), le paramètre n\'est pas du type Widget.');
		}
		catch( EngineException $e)
		{
			echo $e;
			exit();
		}
	}
	

	/**
	 * Méthode assignToWidget
	 * @param $data = array()
	 *			Tableau permettant d'assigner des valeurs aux variables dont le nom se situe dans la clef
	 * @param $widget
	 *			Widget de destination de ces données
	**/
	public function assignToWidget($data=array(), $widget)
	{
		$this->_widget[$widget]->assign($data);
	}

	/**
	 * Méthode baseWidget
	 * Creer les widgets commun à toutes les pages.
	 *
	 * TODO: Touver une autre méthode
	**/
	private function baseWidget()
	{
		foreach(TemplateEngine::$xmlConfig->templates->template as $t)
			if (nl2br($this->_template) == nl2br($t['name']))
    			foreach($t->var as $v)
    			{
    				$ajax = isset($v['ajax']) ? $v['ajax'] : 'FALSE';
    				$tmpW = new Widget((string)$v['name'], (int)$v['cache_duration'], (string)$v['widget'], $ajax);

    				//On récupère les paramètres
    				foreach($v->param as $p)
						$tmpW->assign( array( (string)$p['name'] => $this->getParam($p['method'],$p) ) );

    				$this->addWidget($tmpW);
    			}
	}


	/**
	 * Méthode getParam
	 * Retourne le contenu d'une variable provenant du fichier de configuration
	 * La variable peut être en GET, en POST ou normal (default)
	**/
	private function getParam($method='default', $name=NULL)
	{
		$method = strtolower($method);
		if(empty($name))
		{
			return NULL;
		}
		switch($method)
		{
			case 'get':
				return isset($_GET[(string)$name]) ? htmlentities($_GET[(string)$name]) : NULL;

			case 'post':
				return isset($_POST[(string)$name]) ? htmlentities($_POST[(string)$name]) : NULL;

			case 'widget':
				foreach(TemplateEngine::$xmlConfig->widgets->widget as $w)
				{
					if ($name == $w['name'])
					{
						$ajax = isset($w['ajax']) ? $w['ajax'] : 'FALSE';
	    				$tmpW = new Widget((string)$w['name'], (int)$w['cache_duration'], (string)$w['name'], $ajax);

	    				//On récupère les paramètres
	    				foreach($w->param as $p)
							$tmpW->assign( array( (string)$p['name'] => $this->getParam($p['method'],$p) ) );

						return $tmpW;
					}
				}
				return NULL;

			default:
				return NULL;
		}
		// non atteint
		return NULL;
	}


	/**
	 * Méthode display
	 * Permet l'affichage de la page
	**/
	public function display()
	{
		$error404 = FALSE;
		//On récupère l'affichage de tous les widgets
		if( count($this->_widget) > 0)
		{
			foreach($this->_widget as $widget)
			{
				$html[$widget->getVar()]=$widget->getHTML();
				if($widget->isError404())
				{
					$error404 = TRUE;
					break;
				}
			}
			extract($html); //Extraction de l'html 
		}

		if($error404)
		{
			echo $this->displayError404();
		}
		else // no error 404
		{
			extract($this->_meta); //Extraction des données <meta>

			//On ajoute le script si besoin pour afficher les widgets en ajax
			$arrayWidgetAjax = $this->displayAjax();

			ob_start(); //Ouverture du tampon
			include __VIEWPATH__.$this->_template.'.php'; //On insère le template
			$result = ob_get_contents(); //Récupérons le contenu du tampon
			ob_end_clean(); //Femerture + nettoyage tampon

			$result = templateEngine_translate($result);
			$result = templateEngine_minification($result);

			echo $result; //On affiche le résultat
		}
	}

	public function displayError404()
	{
		header("HTTP/1.0 404 Not Found", true, 404);

		$this->_widget = array();
		$this->_name = 'notfound';
		//Récupération de la configuration de la page
		foreach(TemplateEngine::$xmlConfig->pages->page as  $page)
			if( $this->_name == $page['name'])
			{
    			$this->_template= $page->template;
				$this->_meta['description']= $page->description;
				$this->_meta['title']= $page->title;
				$this->_meta['page_name']= $page['name'];
			}

		// indispensable pour cleaner ajax
		unset($_SESSION['widgetsAjax']);

		$this->baseWidget();

		$contenu = new View('notfound','content');
  		$this->addWidget($contenu);

  		$contenu = new View('searchbar','searchbar');
  		$this->addWidget($contenu);

 		$contenu = new View('footerhome', 'footerhome');
  		$this->addWidget($contenu);

  		return $this->display();
	}

	/**
	 * Méthode displayAjax
	 * Permet l'affichage des widgets avec un appel en ajax
	**/
	public function displayAjax()
	{
		//On ajoute le script si besoin pour afficher les widgets en ajax
		$arrayWidgetAjax = NULL;
		if(isset($_SESSION['widgetsAjax']))
		{
			// token acces ajax
			$tokenAjax = randomName(20);
			$_SESSION['widgetsAjaxToken'] = createToken($tokenAjax);

			$arrayWidgetAjax = '<script type="text/javascript"> var widgetsAjax = ';
			foreach ($_SESSION['widgetsAjax'] as $key => $value) {
				$nameWidgetsAjax[] = $key;
			}
			$arrayWidgetAjax .= json_encode($nameWidgetsAjax).';';
			$arrayWidgetAjax .= '
									function loopAjaxWidget(widgets) {
								        var widget_data = {
								        	token: "'.$tokenAjax.'",
								            widgetName: widgets[0],
								            allWidgets: widgets
								        };

								       $.ajax({
								            url: "/ajaxEngine.php",
								            type: "POST",
								            data: widget_data,
								            dataType: "json",
								            success: function(msg)
								            {
								            	
									                if(msg.validate)
									                {
														$(".widgetAjax_"+msg.item).before(msg.data).remove();
									                }
									                if(msg.widgets)
									                {
									                	loopAjaxWidget(msg.widgets);
									                }
									        	
								            },
								            error: function(msg){}
								        });
								        return false;
									};
									$(document).ready(function(){loopAjaxWidget(widgetsAjax);});
							    </script>';
		}

		return templateEngine_minification($arrayWidgetAjax);
	}

}

//Chargement du fichier de configuration
try
{
	if(!@TemplateEngine::$xmlConfig=simplexml_load_file(__CONFIGFILE__))
		throw new EngineException('TemplateEngine : Fichier de configuration introuvable');
}
catch( EngineException $e)
{
	echo $e;
	exit(); //Sinon l'affichage du site est planté
}