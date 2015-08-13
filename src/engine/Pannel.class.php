<?php

/**
* Classe Page
* @author Guillaume Marques <guillaume.marques33@gmail.com>
*
* LR 10/08/2015
*/

class Pannel {

	private $_position;
	private $_widgets;
	private $_width;
	private $_class;

	function __construct($position){
		$this->_position = $position;
		$this->_widgets = array();
		$this->_width = array();
		$this->_class = array();
	}


	public function getPosition(){
		return $this->_position;
	}

	private function getPannelWidth(){
		$width = 0;
		foreach ($this->_widgets as $key => $value) {
			$width += $key;
		}
		return $width;
	}

	/**
	 *
	 *
	 **/
	public function addWidget($w, $l, $c=''){
		if($this->getPannelWidth() + $l > 12){
			throw new EngineException("Pannel width can't be superior to 12");
		}

		if($w instanceof Widget){
			$this->_widgets[] = $w;
			$this->_width[] = $l;
			$this->_class[] = $c;
		} else {
			throw new EngineException("addWidget($w), le paramètre n\'est pas du type Widget.");
		}
	}

	public function getHTML(){

		ob_start(); //Ouverture du tampon

		echo '<div class="row">';
		foreach( $this->_widgets as $i => $widget){
			echo '<div class="col-md-'.$this->_width[$i].' '.$this->_class[$i].'">';
			echo $widget->getHTML();
			echo '</div>';
		}
		echo '</div>';
		
		$result = ob_get_contents(); //Récupérons le contenu du tampon
		ob_end_clean(); //Femerture + nettoyage tampon
		return $result;
	}
}