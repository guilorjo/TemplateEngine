<?php

/**
* Classe Page
* @author Guillaume Marques <guillaume.marques33@gmail.com>
*
* LR 10/08/2015
*/



class Page {

	public static function defineMetaElements($meta){
		$str = '';
		foreach($meta as $name => $content){
			if($name == 'title'){
				$str .= '<title>'.$content.'</title>';
			} else {
				$str .= '<meta name="'.$name.'" content="'.$content.'"/>';
			}
		}
		return $str;
	}

	public static function addJavascriptDocuments($js){
		$str = '';
		foreach($js as $key => $value){
			$str.= '<script type="text/javascript" src="'.htmlentities($value).'"></script>';
		}
		return $str;
	}

	public static function addCSSDocuments($css){
		$str = '';
		foreach($css as $key => $value){
			$str .= '<link rel="stylesheet" type="text/css" href="'.htmlentities($value).'" media="screen"/>';
		}
		return $str;
	}

	public static function minification($html){
			//Minification
	    $search = array( '/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s');
	    $replace =  array(' ', '');
	    return preg_replace($search, $replace, $html);
	}
}