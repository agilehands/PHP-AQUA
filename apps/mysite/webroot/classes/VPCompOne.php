<?php
/**
  *
  * @created Sep 8, 2011 at 3:35:00 PM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */


use aqua\IViewPortComponent;
class VPCompOne implements  IViewPortComponent{
	public function onViewPortHeader( $viewPort, $state ){
		echo 'header:',$viewPort, ',', $state,'<br/>';
	}
	public function onViewPortRender( $viewPort, $state ){
		echo 'render:',$viewPort, ',', $state,'<br/>';
	}
	public function onViewPortfooter( $viewPort, $state ){
		echo 'footer:',$viewPort, ',', $state,'<br/>';
	}
}
?>