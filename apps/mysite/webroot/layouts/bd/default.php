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
		
	<?php App::viewportHeader(); ?>
	
	</head>
	<body>
		<div class = 'header'>
			<div class='logo'>
				<?php App::img( 'aqua-logo.png' )?>
			</div>
			
			<div class='title'>
				<?php echo _t('title');?>
				<?php Applet::view('locales', 'index')?>
			</div>
		</div>
		<div style="clear: both">
			layout = <?php echo App::GetCurrentLocale() ?>/default.php
			উপরে সাইট টাইটেলে ট্রান্সলেশন ব্যাবহার করাহলেও এই বাক্যটি সরাসরি localized লে-আউটে লেখা হয়েছে।  
		</div>
		<div class='container'>
			<div class='view'>		
				<?php App::currentView() ?>
			</div>
			<div class='sidebar'>
				<?php App::viewPortRender('sidebar'); ?>
			</div>
		</div>		
		<div class='footer'>
			<?php App::viewPortFooter(); ?>
		</div>
			
	</body>	
</html>