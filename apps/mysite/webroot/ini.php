<?php 

use aqua\App;
$comp = new VPCompOne();
App::addToViewPort( 'sidebar',$comp);
App::addToViewPort( 'sidebar', new ViewPortAppletRenderer( 'calculator', 'calc') );
?>