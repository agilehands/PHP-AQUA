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

class DefaultAppDelegate extends AbstractAppDelegate{
	
	
	/**
	 * if returned false url will not be executed
	 * @param $url
	 */
	public function willExecuteURL( &$url ){
		$this->log( 'willExecuteURL', array($url) );
		return true;
	}
	
	public function didExecuteURL( &$url, &$content ){
		$this->log( 'didExecuteURL', array($url) );
		$this->log( "-----------\n\n" );	
	}	
	
	/**
	 * if returned false method will not be called
	 * @param $controller
	 * @param $method
	 * @param $params
	 */
	public function willCallControllerMethod( &$controller, &$method, &$params ){
		$this->log( 'willCallControllerMethod', array( $controller, $method, $method) );
		return true;
	}
	
	public function didCallControllerMethod( &$controller, &$method, &$params ){
		$this->log( 'didCallControllerMethod', array( $controller, $method, $method) );
				
	}
	
	/**
	 * if returned false method will not be called
	 * @param $applet
	 * @param $method
	 * @param $params
	 */
	public function willCallAppletMethod( &$applet, $id, &$method, &$params ){
		$this->log( 'willCallAppletMethod', array( $applet, $id, $method) );
		return true;
	}
	public function didCallAppletMethod( &$applet, $id, &$method, &$params ){
		$this->log( 'didCallAppletMethod', array( $applet, $id, $method));
	}
	
	/**
	 * if returned false method will not be called
	 * @param $applet
	 * @param $method
	 * @param $params
	 */
	public function willCallAppletAction( &$applet, $id, &$method, &$params ){		
		$this->log( 'willCallAppletAction', array( $applet, $id, $method) );
		return true;
	}
	public function didCallAppletAction( &$applet, $id, &$method, &$params ){
		$this->log( 'didCallAppletAction', array( $applet, $id, $method) );		
		App::redirectLast();
	}
	
	/**
	 * NOTE: if returned false zone will not be loaded!!
	 * @param unknown_type $zone
	 * @param unknown_type $path
	 */
	public function willLoadZone( &$zone, &$path ){
		$this->log( 'willLoadZone', func_get_args() );
		return true;
	}
	
	public function didLoadZone( &$zone, &$path ){
		$this->log( 'didLoadZone', func_get_args() );
	}
	
	/**
	 * if returned false method will not be called
	 * @param $controller
	 * @param $method
	 * @param $params
	 */
	public function willCallRESTController( $version, &$controller, &$method, &$params ){
		$this->log( 'willCallRESTController', array( $version, $controller, $method) );
		return true;
	}
	
	public function didCallRESTController( $version, &$controller, &$method, &$params ){
		$this->log( 'didCallRESTController', array( $version, $controller, $method) );
	}
		
	// there should be logger class!
	public function log( $method, $args = array() ){
		//echo $method,'<br/>';
		$date = date('d.m.Y h:i:s'); 
		error_log($date."\t".get_class($this) . '::'.$method ."\t( " . implode(', ', $args) . ")\n");
	}
}

?>