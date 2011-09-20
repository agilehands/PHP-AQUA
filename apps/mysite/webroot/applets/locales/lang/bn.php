<?php
 /**
  *
  * @created Sep 19, 2011 at 2:14:25 AM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

use aqua\App;
?>

<?php

	$lang = array();
	$lang['en'] = 'ইংরেজী ';
	$lang['bn'] = 'বাংলা';
	
	App::updateTransalation( $lang );
?>