<?php

/**
 * 
 *
 * @author: Guillaume Marques <guillaume.marques33@gmail.com>
 * 
 * LR 10/08/15
**/

require_once 'config/global_vars.php';

class InternLink {
	

	function __construct($page, $params, $intern = true){

	}

	public static function get($page, $params=''){
		$link = HTTP_HOST.$page;
		if(!empty($params)){
			foreach($params as $key => $value){
				$link .= "&".$key."=".$value;
			}
		}
		return $link;
	}

	public static function getImg($src){
		return STATIC_IMG.$src;
	}

	public static function ahref($page, $params=''){
		return 'href="'.InternLink::get($page,$params).'"';
	}

	public static function imgsrc($src){
		return 'src="'.InternLink::getImg($src).'"';
	}
}