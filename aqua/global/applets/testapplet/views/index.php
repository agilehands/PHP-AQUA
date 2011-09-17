<?php 
use aqua\Applet;
if(!empty($answered)){
	if(empty($name)){echo "<br><br/>You don't have any name!!?<br/>
	<br/>";}
	else{
	?>
	<br/>
	<br/>
	Your name is: <?php echo $name?>
	<br/>
	<br/>
	<?php
	}
	$this->anchor('index','Try again!');
	return;
}
?>
This is an applet
<br/>

<br/>
<form action="<?= $this->action("homeposted")?>" method="post">
		Enter your name: <input type="text" value="<?php echo $this->state('name');?>" name="name"/>
		<br/>
		<br/>
		<input type="submit" value="Enter your name">
	</form>
	<br>
	