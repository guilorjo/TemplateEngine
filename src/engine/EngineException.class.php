<?php

/**
* Classe EngineException
* @author Guillaume Marques <guillaume.marques33@gmail.com>
* @author Kevin Barreau <kevin.barreau.info@gmail.com>
*
* LR 25/01/2013
*/

define( '__DIVSTYLE__' , 'background: #ffbcc1; width: 800px; margin: 100px auto 100px auto; border-radius: 5px; padding: 30px; border: 1px solid #c6000d;');
define( '__MAILPOSTMASTER__', 'postmaster@yt-stats.com');

class EngineException extends ErrorException
{

	function __construct($message, $id=0, $code=0, $fichier=0, $ligne=0)
	{
		parent::__construct($message, $id, $code, $fichier, $ligne);
	}

	public function __toString()
	{
    
    	$html = '<div style="'.__DIVSTYLE__.'">';
		$html .= 'Oooops! An error has occurred! <br /> Please, reload the page or send a mail at  <b>'.__MAILPOSTMASTER__;
		$html .= '</b> containing the following message: <br /><br />';

		$html .= '<br />[SEVE] '.$this->severity;
		$html .= '<br />[MESS] '.$this->message;
		$html .= '<br />[LOCA] '.$this->file.'  line '.$this->line.'<br /><br />';

		$html .= 'A developer will step in as soon as possible. Thanks! :)';
		$html .='</div>';

		return $html;
	}
}


function error2exception($code, $message, $fichier, $ligne)
{
	throw new EngineException($message, 0, $code, $fichier, $ligne);
}


set_error_handler('error2exception');