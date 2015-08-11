<?php

/**
 * Classe Cache
 * Permet de créer un fichier cache d'un widget (pour les widgets dont le chargement est lent)
 *
 * @author Guillaume Marques <guillaume.marques33@gmail.com>
 * @author Kévin Barreau <kevin.barreau.info@gmail.com>
 * LR 10/08/2015
**/

require_once 'config/global_vars.php';

class Cache
{
	private $_path;
	private $_name;

	private $_duration;
	private $_cacheCreation;


	/**
	 * __construct
	 *
	 * @param String namepage, nom de la page
	 * @param int Duraction, durée de validité du cache en secondes
	 **/
	function __construct($namepage, $duration)
	{
		$this->_name = $namepage;
		$this->_path = CACHE_PATH.$this->_name.CACHE_EXT;
		$this->_duration= $duration;

		$this->_cacheCreation = time();
	}

	/**
	 * params
	 *
	 * Modifie le nom du cache en fonction des paramètres.
	 *
	 * @param String key, nom du paramètre
	 * @param String value, valeur du paramètre
	 **/
	public function params($key, $value)
	{
		$this->_name .= '_'.addslashes($key).':'.addslashes($value);
		$this->_path = CACHE_PATH.$this->_name.CACHE_EXT;
	}

	/**
	 * isCacheExpired
	 *
	 * @return booléean, vrai si le cache est expiré, faux sinon
	**/
	public function isCacheExpired()
	{
		if(!file_exists($this->_path))
			return TRUE;
		else
			return ((time() - filemtime($this->_path)) > ($this->_path+$this->_duration));
	}

	/**
	 * getCacheContent
	 *
	 * @return booléean, vrai si le cache est expiré, faux sinon
	**/
	public function getCacheContent()
	{
		if(file_exists($this->_path))
			return file_get_contents($this->_path);
		return NULL;
	}

	/**
	 * isCacheExpired
	 *
	 * @param String c, le nouveau contenu
	 * @return booléean, vrai si le cache est expiré, faux sinon
	**/
	public function updateCacheContent($c)
	{
		if($this->isCacheExpired() || ($this->getCacheContent()!=$c))
			if(file_put_contents($this->_path, $c))
				return TRUE;
		return FALSE;
	}
}