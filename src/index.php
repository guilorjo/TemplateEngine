<?php
  session_start();

  require_once 'inc/fct.inc.php';
  require_once 'engine/TemplateEngine.class.php';

  $t = new TemplateEngine();

  $contenu = new View('welcome', 'content');
  $t->addWidget($contenu);

  $contenu = new View('searchbar', 'searchbar');
  $t->addWidget($contenu);

  $contenu = new View('footerhome', 'footerhome');
  $t->addWidget($contenu);

  $t->display();
?>