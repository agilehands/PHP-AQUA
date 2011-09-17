<?php
/**
  *
  * @created Sep 8, 2011 at 3:00:53 PM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */
namespace aqua;
class DefaultViewPortDelegate implements IViewPortDelegate{
	public function onViewPortHeader( $viewPort, $state, array &$compoponents ){
		foreach ( $compoponents as $comp){
			$comp->onViewPortHeader( $viewPort, $state );
		}
	}
	public function onViewPortRender( $viewPort, $state, array &$compoponents ){
		foreach ( $compoponents as $comp){
			$comp->onViewPortRender( $viewPort, $state );
		}
	}
	public function onViewPortFooter( $viewPort, $state, array &$compoponents ){
		foreach ( $compoponents as $comp){
			$comp->onViewPortFooter( $viewPort, $state );
		}
	}
}
?>