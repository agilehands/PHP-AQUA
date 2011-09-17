<?php 
use aqua\Applet;
Applet::css( 'testapplet', 'testapp.css', true );
Applet::js( 'testapplet', 'testapp.js',true );
$selected_index = $page=='index'?'selected':'';
$selected_home = $page=='home'?'selected':'';
?>

<div class='app_body'>
	<div class='menu'>
		<div class='menu_item <?php echo $selected_index ?>'>
			<?php echo $this->anchor("index","Home") ?>
		</div>
		<div class='menu_item <?php echo $selected_home ?>'>
			<?php echo $this->anchor("home","About") ?>
		</div>
	</div>
	
	
	<?php
	$this->includeView();
?>
	
</div>
