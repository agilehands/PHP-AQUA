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
namespace aqua\exception;

use aqua\App;

class AquaException extends \Exception{
	public $locale 		= 'n\a';
	public $zoneDir 	= 'n\a';
	public $zone 		= 'n\a';
	
	public $args;
	
	const CONTROLLER_CLASS_FILE_NOT_FOUND 	= 1000;
	const CONTROLLER_NOT_FOUND 				= 1001;	
	const CONTROLLER_METHOD_NOT_FOUND 		= 1002;

			
	const VIEW_NOT_FOUND 				= 2000;
	const LAYOUT_NOT_FOUND 				= 2001;
	
	const APPLET_CLASS_NOT_FOUND 		= 3000;
	const APPLET_NOT_FOUND 				= 3001;
	const APPLET_METHOD_NOT_FOUND 		= 3002;
	const APPLET_VIEW_NOT_FOUND 		= 3003;
	const APPLET_VIEW_DIR_NOT_FOUND 	= 3004;
	const APPLET_INVALID_ACTION_URL 	= 3005;
	const APPLET_ASSET_NOT_FOUND 		= 3006;
	const APPLET_GLOBAL_ASSET_LINK_ASKED = 3007;
	const APPLET_LAYOUT_NOT_FOUND 		= 3008;
	
	
	const ASSET_NOT_FOUND 	= 4000;	
	const PROGRAM_ERROR 	= 5000;
	
	const DB_ERROR = 6000;
	const DB_CONF_NOT_FOUND = 6001;
	const DB_FAILED_TO_CONNECT = 6002;
	
	const VP_ERROR = 7000;
	const VP_NOT_VALID_DELEGATE = 7001;
	const VP_NOT_VALID_COMPONENT = 7002;
	
	const REST_ERROR = 80000;
	const REST_NO_NOUN = 80001;
	const REST_UNKNOWN_CONTROLLER = 80002;
	const REST_CONTROLLER_FILE_NOT_FOUND = 80003;
	const REST_CONTROLLER_CLASS_NOT_FOUND = 80004;
	const REST_CONTROLLER_METHOD_NOT_FOUND = 80005;
	

	private $messages = array(
					 0=>'Unknown Error!'
					,self::CONTROLLER_CLASS_FILE_NOT_FOUND=>'Controller Class file not found :  <b><i>%s</i></b>'
					,self::CONTROLLER_NOT_FOUND=>'Controller not found : <b><i>%s</i></b>'
					,self::CONTROLLER_METHOD_NOT_FOUND=>'Controller method not found :  <b><i>%s::%s [ or index ]</i></b>'
					
					,self::VIEW_NOT_FOUND=>'View file not found :  <b><i>%s</i></b>'
					,self::LAYOUT_NOT_FOUND=>'Layout file not found :  <b><i>%s</i></b>'
					
					,self::APPLET_CLASS_NOT_FOUND=>'Applet Class file not found :  <b><i>%s</i></b>'
					,self::APPLET_NOT_FOUND=>'Controller not found :  <b><i>%s</i></b>'
					,self::APPLET_METHOD_NOT_FOUND=>'Applet method not found :  <b><i>%s::%s</i></b>'
					,self::APPLET_VIEW_NOT_FOUND=>'Applet View file not found :  <b><i>%s/views/%s</i></b>'
					,self::APPLET_VIEW_DIR_NOT_FOUND=>'Applet View dir not found :  <b><i>%s/views/</i></b>'
					,self::APPLET_INVALID_ACTION_URL=>'Applet action url must be like :  <b><i>appletclass/id/[optional method url]</i></b>'
					,self::APPLET_ASSET_NOT_FOUND=>'Applet asset not found :  <b><i>%s/asset/%s</i></b>'
					,self::APPLET_GLOBAL_ASSET_LINK_ASKED=>'Global Applets\' assets cannot be used as link :  <b><i>%s/asset/%s</i></b><br/>'
					,self::APPLET_LAYOUT_NOT_FOUND=>'Applet layout not found :  <b><i>%s/asset/%s</i></b><br/>'
					
					,self::ASSET_NOT_FOUND=>'Asset not fond :  <b><i>%s</i></b>'
					,self::PROGRAM_ERROR=>'Program error  <b><i>%s</i></b>'
					
					,self::DB_ERROR=>'Database error! <br/><b>%s</b'
					,self::DB_CONF_NOT_FOUND=>'Database Configuration not found: <b><i>%s</i></b>'
					,self::DB_FAILED_TO_CONNECT=>'Failed to connect database server'
					
					,self::VP_ERROR=>'View port error: <b>%s</b>'
					,self::VP_NOT_VALID_DELEGATE=>'View port delegates must implement <code>IViewPortDelegate</code>:<b>%s</b>'
					,self::VP_NOT_VALID_COMPONENT=>'View port components must implement <code>IViewPortComponent</code>:<b>%s</b>'
					
					,self::REST_ERROR=>'REST error'
					,self::REST_NO_NOUN=>'REST error:version:<b><i>%s</i></b>'
					,self::REST_UNKNOWN_CONTROLLER=>'REST error: version:<b><i>%s</i></b>, unknown controller: <b><i>%s</i></b>'
					,self::REST_CONTROLLER_FILE_NOT_FOUND=>'REST error: version:<b><i>%s</i></b>,controller file not found: <b><i>%s</i></b>'
					,self::REST_CONTROLLER_CLASS_NOT_FOUND=>'REST error: version:<b><i>%s</i></b>,controller class not found: <b><i>%s</i></b>'
					,self::REST_CONTROLLER_METHOD_NOT_FOUND=>'REST error::version:<b><i>%s</i></b>, controller method not found:<b><i>%s :: %s</i></b>'
				);
	public function __construct( $code, $args = array() ){
		if(!is_array($args)){
			$args=  array($args);
		}
		
		parent::__construct( vsprintf( $this->messages[ $code ] , $args), $code);
	
		$this->locale 		= App::$instance->currentLocale;
		$this->zoneDir 		= App::$instance->currentZoneDir;		
		$this->zone 		= App::$instance->currentZone;
		
		$this->args = $args;
	}
}

?>