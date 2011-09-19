<?php
	use aqua\IViewPortDelegate;
use aqua\App;
	use aqua\Controller;

	class _default extends Controller implements IViewPortDelegate{	
		public function __construct( ){
			$this->setLayout('default');
			
			// you can add a viewport with delegate
			//App::addViewPort( 'sidebar', $this);
			
			// or directly add component ot viewports
			App::addToViewPort( 'sidebar', new ViewPortAppletRenderer( 'testapplet', 'ta') );				
		}	
		
		public function index(){
			$this->show( 'index' );
		}
		
		public function other(){
			$this->show( 'other' );
		}

		public function onViewPortHeader( $viewPort, $state, array &$compoponents ){
			echo 'header:',$viewPort,', state = ', $state,'<br/>';
		}
		public function onViewPortRender( $viewPort, $state, array &$compoponents ){
			foreach ( $compoponents as $comp){
				$comp->onViewPortRender( $viewPort, $state );
			}
		}
		public function onViewPortFooter( $viewPort, $state, array &$compoponents ){
			echo 'footer:',$viewPort, ', state = ',$state,'<br.>';
		}
	}