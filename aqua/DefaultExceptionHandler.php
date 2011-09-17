<?php
/**
 *         
 * #######################################################################
 *           +----------------------------------------------+
 *           | THIS FILE IS A PART OF "PHP AQUA" FRAMEWORK | 
 *           +----------------------------------------------+
 *           
 *  	THIS CODE IS PROTECTED UNDER Apache Software License
 *  	http://www.apache.org/licenses/LICENSE-2.0
 *
 * 	Simply stating :
 * 		Proprietary Software linking: Yes
 * 		Distribution of this software : Yes
 * 		Redistributing of the code with changes: You can
 * 		Compatible with GNU GPL : NO :D
 *
 * 	Feel free to change and use but don't need to share the code though highly appreciated!
 * 
 * @author Shaikh Sonny Aman <agilehands@gmail.com>
 * 	
 * #######################################################################
 */
namespace aqua;
use aqua\exception\AquaException;

class DefaultExceptionHandler implements IExceptionHandler{
			
	public function onAquaException( \aqua\exception\AquaException $ae){					
		include 'exceptions/views/AquaException.php';
	}
	public function onProgramError( \aqua\exception\AquaException $ae){
		extract( $ae->args);
		include 'exceptions/views/ProgramError.php';
	}
	public function onRESTException( AquaException $ae){
		echo $ae->getMessage();
		App::setHTTPError( 400 );
	}
	public function onException( \Exception $e ){
		$this->displayError( 'Undefined error', $e->getMessage());
	}
	
	
	public function displayError( $title, $message){
		include 'exceptions/views/Exception.php';
	}
	
	
	public function format( &$output ){
		$output = str_replace('#', '<br/>#', $output );
	}
}
?>