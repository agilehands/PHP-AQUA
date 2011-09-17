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

abstract class AbstractAppDelegate{	
	
	/**
	 * if returned false url will not be executed
	 * @param $url
	 */
	public function willExecuteURL( &$url ){		
		return true;
	}
	
	public function didExecuteURL( &$url, &$content ){}	
	
	/**
	 * if returned false method will not be called
	 * @param $controller
	 * @param $method
	 * @param $params
	 */
	public function willCallControllerMethod( &$controller, &$method, &$params ){
		return true;
	}
	
	public function didCallControllerMethod( &$controller, &$method, &$params ){}
	
	/**
	 * if returned false method will not be called
	 * @param $applet
	 * @param $method
	 * @param $params
	 */
	public function willCallAppletMethod( &$applet, $id, &$method, &$params ){	
		return true;
	}
	
	public function didCallAppletMethod( &$applet, $id, &$method, &$params ){}
	
	/**
	 * if returned false method will not be called
	 * @param $applet
	 * @param $method
	 * @param $params
	 */
	public function willCallAppletAction( &$applet, $id, &$method, &$params ){		
		return true;
	}
	public function didCallAppletAction( &$applet, $id, &$method, &$params ){}
	
	/**
	 * NOTE: if returned false zone will not be loaded!!
	 * @param unknown_type $zone
	 * @param unknown_type $path
	 */
	public function willLoadZone( &$zone, &$path ){	
		return true;
	}
	
	public function didLoadZone( &$zone, &$path ){}
	
	/**
	 * if returned false method will not be called
	 * @param $controller
	 * @param $method
	 * @param $params
	 */
	public function willCallRESTController( $version, &$controller, &$method, &$params ){
		return true;
	}
	
	public function didCallRESTController( $version, &$controller, &$method, &$params ){}
}

?>