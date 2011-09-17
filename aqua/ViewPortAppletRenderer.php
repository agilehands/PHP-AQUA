<?php
/**
  *
  * @created Sep 10, 2011 at 10:36:39 PM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

use aqua\Applet;
use aqua\IViewPortComponent;

class ViewPortAppletRenderer implements IViewPortComponent{
	private $appletName;
	private $appletID;
	private $appletView;
	private $appletViewArgs;
	
	public function __construct( $applet, $id, $view='', $args = array()){
		$this->appletName 	= $applet;
		$this->appletID 	= $id;
		$this->appletView 	= $view;
		
		$this->appletViewArgs = $args;
				
	}
	public function onViewPortHeader( $viewPort, $state ){
		
	}
	public function onViewPortRender( $viewPort, $state ){		
		Applet::render( $this->appletName, $this->appletID, $this->appletView, $this->appletViewArgs );		
	}
	public function onViewPortfooter( $viewPort, $state ){
		
	}
}
?>
