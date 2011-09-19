<?php 
use aqua\App;
use aqua\SimpleRelationalRecord;
use aqua\MysqlAgent;
use aqua\Applet;


?>

this is a view. 
<br/>
<br/>
Header colors are changed by localized css.

<br>
<br/>
Go to <?php App::anchor('other', 'Another view') ?>.
<?php
//$model = new MysqlAgent();
//$model->connect();
//$srr = new SimpleRelationalRecord();
//$srr->init( 'users',array('idusers'=>'1'));

//$srr->addRelation('comments','idusers','idusers');

?>
