<?php

/**
 *	Class HtmlText
 *	Il ne s'agit juste que d'un widget ne contenant que du code html..
 *
 *  @author Guillaume Marques <guillaume.marques33@gmail.com>
 *  LR 05/01/2013
 *
 * /!\ Utilisation déconseillée
*/

require_once 'Widget.class.php';
require_once 'inc/fct.inc.php';

class HtmlText extends Widget
{
	private $_text;

	function __construct($text, $var='')
	{
		parent::__construct('HtmlText_'.randomName(10), $var);
		$this->_text = $text;
	}

	public function setText($t)
	{
		$this->_text = $t;
	}

	public function getHTML()
	{
		$html =  '<!-- Generation: '.date('d/m/Y H:i:s').'  -- Widget: '.$this->_name.' -->'."\n";
		$html .= "\t".$this->_text;
		$html .= "\n";

		return $html;
	}

}