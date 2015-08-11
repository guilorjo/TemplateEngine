<?php

/**
* Classe Page
* @author Guillaume Marques <guillaume.marques33@gmail.com>
*
* LR 10/08/2015
*/



class Page {

	public static function defineMetaElements($meta){

	}

	public static function addJavascriptDocuments($js){
		foreach($js as $key => $value){
			echo '<script type="text/javascript" src="'.htmlentities($value).'"></script>';
		}
	}

	public static function addCSSDocuments($css){
		foreach($css as $key => $value){
			echo '<link rel="stylesheet" type="text/css" href="'.htmlentities($css).'" media="screen"/>';
		}
	}

	public static function minification($html){
			//Minification
	    $search = array(
	        '/ {2,}/',
	        '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
	    );

	    $replace =  array(
	        ' ',
	        ''
	    );
	    return preg_replace($search, $replace, $html);
	}
}