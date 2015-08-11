<?php

/**
* Classe EngineException
* @author Guillaume Marques <guillaume.marques33@gmail.com>
* @author Kevin Barreau <kevin.barreau.info@gmail.com>
*
* LR 15/08/2015
*/

require_once 'config/global_vars.php';

class EngineException extends ErrorException
{

	function __construct($message, $id=0, $code=0, $fichier=0, $ligne=0)
	{
		parent::__construct($message, $id, $code, $fichier, $ligne);
	}

	public function __toString()
	{

    	$content = '<div class="container"><br /><br /><br /><div class="alert alert-danger">';
		$content .= 'Oooops! An error has occurred! <br /> Please, reload the page or send a mail at  <b>'.MAIL_POSTMASTER;
		$content .= '</b> containing the following message: <br /><br />';

		$content .= '<br />[SEVE] '.$this->severity;
		$content .= '<br />[MESS] '.$this->message;
		$content .= '<br />[LOCA] '.$this->file.'  line '.$this->line.'<br /><br />';

		$content .= 'A developer will step in as soon as possible. Thanks!';
		$content .='</div></div>';

		ob_start();
		include HTML_ERROR;

		$result = ob_get_contents();

		ob_end_clean();
		return $result;
	}
}


function error2exception($code, $message, $fichier, $ligne)
{
	throw new EngineException($message, 0, $code, $fichier, $ligne);
}


set_error_handler('error2exception');