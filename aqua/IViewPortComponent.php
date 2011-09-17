<?php
/**
  *
  * @created Sep 8, 2011 at 3:04:52 PM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

namespace aqua;

interface IViewPortComponent{
	public function onViewPortHeader( $viewPort, $state );
	public function onViewPortRender( $viewPort, $state );
	public function onViewPortfooter( $viewPort, $state );
}
?>