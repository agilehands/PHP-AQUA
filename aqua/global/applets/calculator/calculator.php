<?php
/**
  *
  * @created Sep 19, 2011 at 12:24:08 AM
  * @author Shaikh Sonny Aman <agilehands@gmail.com>
  *               http://amanpages.com
  */

use aqua\Applet;
class calculator extends Applet{
	public function __construct( $id, $dir ){
		parent::__construct($id, $dir);		
		$this->setLayout(false);// applets can also have layouts!
	}
	public function index( ){
		$this->show('prompt');
	}
		
	public function add(){
		$a = $this->post('num_a');
		$b = $this->post('num_b');
		$msg = 'Please enter valid numbers';
		if( is_numeric( $a) && is_numeric( $b ) ){
			$msg = 'Answer is:'.($a + $b);	
		}
		$this->once( 'msg',$msg );
		$this->execute('index');
	}
}
?>