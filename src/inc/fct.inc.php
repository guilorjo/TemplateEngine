<?php

/* Fonctions */
function randomName($taille) //Utilisée par TemplateEngine
{
	$string = "";
	$chaine = "abcdefghijklmnpqrstuvwxy";
	srand((double)microtime()*1000000);

	for($i=0; $i<$taille; $i++)
		$string .= $chaine[rand()%strlen($chaine)];

	return $string;
}




//Permet de traduire le texte balisé avec {t}
//Pas encore implémenté
function templateEngine_translate($buffer)
{
	preg_match_all("#\{t\}(.+)\{/t\}#i", $buffer, $matches);

	for($i=0; $i<count($matches[0]); $i++)
		$buffer=str_replace($matches[0][$i], _($matches[1][$i]), $buffer);

    return $buffer;
}


/*Connexion à la base de donnée*/
function dbConnexion()
{
	//return new PDO('mysql:host=name_host;dbname=name_db','user','pass');
}
