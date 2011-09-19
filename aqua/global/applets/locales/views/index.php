<?php
/**
  *
  * @created Sep 19, 2011 at 2:05:01 AM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

use aqua\App;
use aqua\Applet;

Applet::css('locales', 'style.css');

$locales = App::getLocales();
$currentLocale = App::currentLocale();
foreach ( $locales as $lang){
	if( $currentLocale == $lang ){
		?>
	<a style="text-decoration:none;font-size:14px" href="<?php echo App::url('',$lang)?>"> <?php  Applet::img('locales', $lang.'.png' ,$lang,'selected_locale')?>  </a>
	<?php
	}else{
		?>
	<a style="text-decoration:none;font-size:14px" href="<?php echo App::url('',$lang)?>"> <?php  Applet::img('locales', $lang.'.png',$lang, 'other_locale' )?>  </a>
	<?php
	}
}
?>