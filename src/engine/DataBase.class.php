<?php

/**
 *
 * Guillaume Marques <guillaume.marques33@gmail.com>
 * LR 11/08/2015
 **/

require_once 'config/global_vars.php';

class DataBase {

    private static $_instance = null;

    private function __construct() {  
        if($db_link = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_BASE)){
            $this->_instance = $db_link;
        } else {
            throw new EngineException("Impossible de se connecter à la base de données");
        }
    }

    public static function getLink() {
        if(is_null(self::$_instance)) {
            self::$_instance = new DataBase();  
        }
        return self::$_instance;
    }
}