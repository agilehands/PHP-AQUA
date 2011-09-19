<?php
/**
  *
  * @created Sep 19, 2011 at 2:05:01 AM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

use aqua\App;
$locales = App::getLocales();

foreach ( $locales as $lang){
	echo '<a style="text-decoration:none;font-size:14px" href="'.App::url('',$lang).'"> |'._t($lang)  .' </a>';
}
?>
 <span style='font-size: small; color:#dddddd'>| (use App::url() to change any location to any language) </span>