<?php
/**
 * Classe View
 * Widget ne contenant pas de modèle mais uniquement une vue
 *
 * @author Guillaume Marques <guillaume.marques33@gmail.com>
 * LR 11/08/2015
 *
 **/

require_once 'config/global_vars.php';
require_once 'Widget.class.php';
require_once 'inc/fct.inc.php';

class View extends Widget
{

	private $_view;

	/**
	 * __construct
	 *
	 * @param String:view, nom du fichier contenant la vue
	 * @param String::var, nom de la variable du template qui contiendra ce widget (facultatif)
	**/
	function __construct($view, $var='')
	{
		parent::__construct('View_'.randomName(10), 0, $var);
		$this->_view = $view;
	}

	/**
	 * setView
	 * Modification du fichier contenant la vue
	 *
	 * @param String::newView,  nom du fichier contenant la vue
	**/
	public function setView($newView)
	{
		$this->_view = $newView;
	}

	/**
	 * getHTML
	 * surcharge de Widget::getHTML
	 *
	 * @return code HTML de la vue
	**/
	public function getHTML()
	{
	
		extract($this->_content);

		ob_start(); //On démarre la cache

		echo '<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->';

		include VIEW_PATH.$this->_view.'.php';	

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}