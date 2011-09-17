<?php

	use aqua\Applet;
class testapplet extends Applet{
		public function __construct( $id, $dir ){
			parent::__construct($id, $dir);
			$this->setLayout('default');
			
		}
		public function index( $name = '', $answered= ''){
			
			$this->addLayoutParam( 'page', 'index');			
			$this->show('index',array('name'=>$name,'answered'=>$answered));			
		}
		public function home(){
			$this->addLayoutParam( 'page', 'home');			
			$this->show('home');						
		} 
		
		public function homeposted( ){
			if( $this->isPosted() ){
				$this->execute( 'index', array( $this->post( 'name' ),'answered' ));
				return;
			}			
		}
	}
?>