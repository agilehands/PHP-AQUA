<?php 
use aqua\App;
use aqua\SimpleRelationalRecord;
use aqua\MysqlAgent;
use aqua\Applet;


App::viewportHeader();

?>
<hr/>
<h1>Please help us making a better demo.</h1>
<hr/>
<hr/>

<?php
App::addToViewPort( 'vpone', new ViewPortAppletRenderer( 'testapplet', 'ta') );
App::viewPortRender('vpone');

App::viewPortRender('vptwo', App::VIEWPORT_STATE_VISIBLE);

$model = new MysqlAgent();
$model->connect();
$srr = new SimpleRelationalRecord();
$srr->init( 'users',array('idusers'=>'1'));

$srr->addRelation('comments','idusers','idusers');


App::viewPortFooter();
?>
