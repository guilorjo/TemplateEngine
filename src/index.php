<?php
  session_start();

  require_once 'inc/fct.inc.php';
  require_once 'engine/TemplateEngine.class.php';

  $t = new TemplateEngine();


  /*Example*/

  $contenu = new View('welcome', 'content');
  $t->addWidget($contenu);

  $contenu = new Widget('header', 0*3600, 'header');
  $contenu->assign();

  $t->display();
?>