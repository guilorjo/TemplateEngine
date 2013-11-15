<?php

/**
* Classe CompositeWidget
* Assemblage de widgets
*
* @author Guillaume Marques <guillaume.marques33@gmail.com>
* LR 25/01/2013
*
**/

require_once 'Widget.class.php';
require_once 'inc/fct.inc.php';

class CompositeWidget extends Widget
{
	private $_widgets = array();


	function __construct($var)
	{
		parent::__construct('CompositeWidget_'.randomName(10), 0, $var);
	}


	public function addWidget($w)
	{
		try
		{
			if($w instanceof Widget)
				$this->_widgets[$w->getName()]= $w;
			else
				throw new EngineException('addWidget($w), le paramètre n\'est pas du type Widget.');
		}
		catch( EngineException $e)
		{
			echo $e;
			exit();
		}
	}

	//Surcharge de la méthode getHTML
	public function getHTML()
	{
		
		$html='<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->'."\n";
		foreach($this->_widgets as $widget)
			$html .= $widget->getHTML()."\n\n";

		return $html;
	}
}