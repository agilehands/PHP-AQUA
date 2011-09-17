<?php
/**
  *
  * @created Sep 8, 2011 at 2:59:13 PM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

namespace aqua;
interface IViewPortDelegate{
	public function onViewPortHeader( $viewPort, $state, array &$compoponents );
	public function onViewPortRender( $viewPort, $state, array &$compoponents );
	public function onViewPortFooter( $viewPort, $state, array &$compoponents );	
}
?>