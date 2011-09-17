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

interface IExceptionHandler extends IOutputFormatter{
	public function onAquaException( \aqua\exception\AquaException $ae);
	public function onProgramError( \aqua\exception\AquaException $ae);
	public function onRESTException( \aqua\exception\AquaException $ae);
	public function onException( \Exception $e );
	public function displayError( $title, $message);
}
?>