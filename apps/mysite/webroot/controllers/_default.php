<?php
	use aqua\IViewPortDelegate;
use aqua\App;
	use aqua\Controller;

	class _default extends Controller implements IViewPortDelegate{	
		public function __construct( ){
			$this->setLayout('default');
			
			$comp = new VPCompOne();
			App::addToViewPort( 'vpone',$comp);

			App::addViewPort( 'vptwo', $this );
			App::addToViewPort( 'vptwo', $comp);
			App::setViewPortState( 'vptwo', App::VIEWPORT_STATE_VISIBLE);
		}	
		
		public function index(){
			$this->show( 'index' );
		}

		public function onViewPortHeader( $viewPort, $state, array &$compoponents ){
			echo 'header:',$viewPort,', state = ', $state,'<br/>';
		}
		public function onViewPortRender( $viewPort, $state, array &$compoponents ){
			echo 'render:',$viewPort, ', state = ',$state,'<br/>';
		}
		public function onViewPortFooter( $viewPort, $state, array &$compoponents ){
			echo 'footer:',$viewPort, ', state = ',$state,'<br.>';
		}
	}