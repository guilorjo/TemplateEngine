<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
	Template pour le site de demonstration
	Guillaume Marques <guillaume.marques33@gmail.com>
-->

	<head>

		<!-- Do not change the following lines -->
		<title><?php echo $title; ?></title>
		<meta name="description" content="<?php echo $description; ?>" />
		<!-- end -->

		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>

		<link rel="stylesheet" type="text/css" href="/static/css/main.css" />
      	<link rel="stylesheet" type="text/css" href="/static/css/bootstrap.min.css" />

      	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js" ></script>  

    </head>
    <body>

    	<header>
    		<div id="header-inside">
    			<?php echo $header_var; ?>
    		</div>
    	</header>
		<div id="content">
          	<div id="content-inside">
             	<?php echo $content; ?>
          	</div><!-- div#content-inside -->
        </div><!-- div#content -->
        <footer>
        	<div id="footer-inside">
        		<?php echo $footer_var; ?>
        	</div>
        </footer>
    </body>
</html>