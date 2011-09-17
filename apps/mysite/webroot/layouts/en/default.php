<?php
use aqua\Applet;
use aqua\App;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="AQUA is highly extensible and flexible php mvc framework. It support restful application development in a intuitive manner and promotes resuable, component based modular development.
	It is aimed for modular and team oriented development by introducing zones and applet!" />
	
	<meta  name="keywords" content="php, php-framework, mvc, codeigniter, yii, fastest php framework, cakephp, symphony, kohana, agile php, agile " />
		<title>PHP AQUA  :: <?= _t('welcome') ?></title>
		<?php App::css('style.css' ) ?>		 		
		<?php App::jsExternal( 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js' ) ?>
		<?php App::js('global.js') ?>
				
	</head>
	<body>
		<?php App::img( 'aqua-logo.png' )?>
		<br/>		
		<?php App::currentView() ?>		
	</body>	
</html>