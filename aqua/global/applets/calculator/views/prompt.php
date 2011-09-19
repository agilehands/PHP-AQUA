<?php
/**
  *
  * @created Sep 19, 2011 at 12:29:07 AM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */
$msg = $this->once('msg');
?>
<div style="padding:10px; background:#eeeeee;min-height:100px;margin-bottom:30px;">
This is a calculator applet!
<br/>
<br/>
<form action="<?= $this->action("add")?>" method="post">
A:<input type='text' name='num_a'/>
<br/>
B:<input type='text' name='num_b'/>
<br/>
<br/>
<?php 
if( !empty($msg)){
	echo $msg,'<br/>';
}
?>
<input type='submit' value="add">

</form>
</div>