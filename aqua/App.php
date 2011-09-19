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
 *
 *
 *	http://www.phpaqua.com
 * 
 *
 *
 *@author Shaikh Sonny Aman <agilehands@gmail.com>
 *
 * 
 * #######################################################################
 */
namespace aqua;
use aqua\exception\AquaException;

class App{	

  	public $directory;
	public $baseURL;
	public $locales;
	public $defaultLocale;
	public $currentLocale;
	public $currentZone;
	public $currentZoneDir;
	
	
	private $currentController;
	private $currentControllerClass;
	
	
	public $delegates;	
	public $currentURL;// url without locale or any triggers and WITH get params
	public $actionURL;// url without locale or any triggers and get params
	public $zoneStack = array();
	public $isAppletAction = false;
	
	public static $instance = null;
	private static $clone = null;
	
	private $RESTNouns = null;
	private $urlExtension = null;
	private $isRestZone = false;
	private $RESTCurrentVersion = 'v1';	
	private $RESTAvailableVersions = null;
	
	
	/**
	 * This method is to be called within ini.php file in the rest zone
	 * @param string $currentVersion
	 */
	public static function setRestZone( array $nouns, array $versions, $currentVersion ){
		self::$instance->RESTNouns = $nouns;
		self::$instance->isRestZone = true;
		self::$instance->RESTAvailableVersions = $versions;
		self::$instance->RESTCurrentVersion = $currentVersion;
	}
	
	private $httpErrorHandler = null;
	public function setHTTPErrorHandler( IHTTPErrorHandler $handler ){
		$this->httpErrorHandler = $handler;
	}
	
	public static function setHTTPError( $HTTPErrorCode, $errorMessage = '', $HTTPVersion = '1.0' ){
		self::$instance->httpErrorHandler->onHTTPError( $HTTPErrorCode, $errorMessage = '', $HTTPVersion = '1.0' );
	}
			
	/**
	 * Constructor
	 * 
	 * @param string $directory 	
	 * @param string $localServer
	 * @param string $localBase		
	 * @param string $hostServer
	 * @param string $hostBase
	 * @param string $defaultLocale
	 * @param array $locales
	 */
	public function __construct(  $directory
									, $deployments
									, $defaultLocale 	= 'en'
									, $locales 			= array('en','bn')
									){		
		$this->directory 		= $directory;
		$this->deployments 		= $deployments;
		$this->defaultLocale 	= $defaultLocale;
		$this->locales 			= $locales;
		$this->appName 		= basename( $directory );
		
		$this->currentLocale 	= $defaultLocale;		
		
		$this->currentDeployment = $deployments[ $_SERVER[ 'HTTP_HOST' ] ];
			$this->baseURL = (!empty($_SERVER['HTTPS'])? "https://" :'http://') . $_SERVER['HTTP_HOST'] . $this->currentDeployment['url_base']; 
		
		set_include_path( $directory . '/classes' . PATH_SEPARATOR . get_include_path() );
				
		set_error_handler("AquaErrorHandler");
		
		// not singleton.. just shared instance used in other public functions. 
		// used this way to use auto completion for editors like eclipse.
		// well.. autocompletion is an important feature... at least fo me :p
		
		self::$clone = $this;
		self::$instance = &$this;
		
		$this->delegates = array();		
		$this->addDelegate( new DefaultAppDelegate( $this ) );
		
		$this->addOutputFormatter( new DefaultFormatter() );
		$this->setExceptionHandler( new DefaultExceptionHandler() );
		$this->setHTTPErrorHandler( new DefaultHTTPErrorHandler() ); 
		
	}
	
	public function addDelegate( AbstractAppDelegate $delegate ){
		$this->delegates[] = $delegate;
	}
	
	public function setExceptionHandler( IExceptionHandler $handler ){
		$this->exceptionHandler = $handler;		
	}
	
	public function setLocales( array $locales ){
		$this->locales = $locales;
	}
	/**
	 * Starts the app	
	 */
	public function run(){
		// output buffer
		ob_start();
		
		// start session
		session_start();
		
		$url = $_SERVER['REQUEST_URI'];
		try {
			// check it should be loaded
			if( $this->willExecuteURL($url) ){ // nope	
				
					$urlParts = $this->makeURLParts($url);
					
					$this->isAppletAction = $this->isAppletUrl( $urlParts );				
					$this->currentLocale 	= $this->loadLocale( $urlParts );
					
					$this->actionURL = implode( '/', $urlParts);
					
					$this->currentZone 		= 'webroot';
					$this->currentZoneDir 	= $this->loadZone( $this->directory . '/webroot', $urlParts );
					array_shift($this->zoneStack);// remove webroot from stack
					
					if( $this->isAppletAction ){
					
						$this->runApplet( $urlParts );
					}else{
						if( $this->isRestZone === true ){
							$this->runRestController( $urlParts );
						}else{
							$this->runController( $urlParts );
							
							// save current url as last url
							$_SESSION[ SESSION_PREFIX.'LAST_URL'] = $this->currentDeployment['url_base'] .  $this->currentURL;				
						}
					}						
			}		
				
			$output = ob_get_contents();
			ob_end_clean();
			
			$this->applyOutputFormatters( &$output );
			$this->didExecuteURL( $url, &$output );
		}catch ( \aqua\exception\AquaException $e) {
			ob_clean();
			if( $e->getCode() ==  \aqua\exception\AquaException::PROGRAM_ERROR ){
				$this->addOutputFormatter( $this->exceptionHandler );
				$this->exceptionHandler->onProgramError( $e );
			}else if( $e->getCode() >= AquaException::REST_ERROR ){
				$this->exceptionHandler->onRESTException( $e );
			}else{
				$this->addOutputFormatter( $this->exceptionHandler );
				$this->exceptionHandler->onAquaException( $e );
			}
			die();
		}	 
		catch ( \Exception $e) {
			ob_clean();
			// get class name
			$this->addOutputFormatter( $this->exceptionHandler );			
			$this->exceptionHandler->onException($e);
						
			die();
		}	
		echo $output;
	}
	
	/**
	 * This method does same as the run method but returns the output.
	 * 
	 * Can be used to consume some rest api URI within the app or testing.
	 * 
	 * This method must be called within try catch-block!
	 * 
	 * @param $url URL
	 */
	public static function execute( $url ){
		$clone = self::$instance;
		
		ob_start();
		
		// start session
		session_start();
		
		// check it should be loaded
		if( $clone->willExecuteURL($url) ){ // nope	
			
				$urlParts = $clone->makeURLParts($url);
				
				$clone->isAppletAction = $clone->isAppletUrl( $urlParts );				
				$clone->currentLocale 	= $clone->loadLocale( $urlParts );
				
				$clone->actionURL = implode( '/', $urlParts);
				
				$clone->currentZone 		= 'webroot';
				$clone->currentZoneDir 	= $clone->loadZone( $clone->directory . '/webroot', $urlParts );
				array_shift($clone->zoneStack);// remove webroot from stack
				
				if( $clone->isAppletAction ){
				
					$clone->runApplet( $urlParts );
				}else{
					if( defined('REST_ZONE') ){
						$clone->restController( $urlParts );
					}else{
						$clone->runController( $urlParts );
						
						// save current url as last url
						$_SESSION[ SESSION_PREFIX.'LAST_URL'] = $clone->currentDeployment['url_base'] .  $clone->currentURL;				
					}
				}						
		}		
			
		$output = ob_get_contents();
		ob_end_clean();
		
		$clone->applyOutputFormatters( &$output );
		$clone->didExecuteURL( $url, &$output );
		
		return $output;
	}
	
	private function runRestController( &$urlParts ){
		$version = $this->RESTCurrentVersion;
						
		// no api directory browsing
		if( empty( $urlParts) ){
			throw new AquaException( AquaException::REST_NO_NOUN, array( $version ));
		}
				
		$httpVerb = strtolower( $_SERVER['REQUEST_METHOD'] );		
				
		$resourceType = $this->urlExtension;
		
		if( in_array( $urlParts[0], $this->RESTAvailableVersions ) ){
			$version = array_shift( $urlParts );
			if( empty( $urlParts) ){
				throw new AquaException( AquaException::REST_NO_NOUN, array( $version ) );
			}
		}
		
		$functionName = $httpVerb;
		$data = array();
				
		$currentNoun = array_shift( $urlParts );
		if( in_array( $currentNoun, $this->RESTNouns ) ){ // check if valid noun
			$data[ $currentNoun ] = array();
		}else{			
			throw new AquaException( AquaException::REST_UNKNOWN_CONTROLLER, array( $version, $currentNoun ) );
		}
		
		
		
		$controller = $currentNoun;
		$controllerObj = 0;
		$functionName .= '_'.$currentNoun;
		
		
		foreach ( $urlParts as $part ){
			if( in_array( $part, $this->RESTNouns ) ){ // got a noun				
				// update current noun
				$currentNoun = $part;
				
				$data[ $currentNoun ] = array();
				$functionName .= '_'.$currentNoun;
				
			}else{// collect arguments		
				$data[ $currentNoun ][] = $part;
			}
		}
		
		
		//include controller file
		if(!file_exists( $this->currentZoneDir . '/controllers/'.$version.'/'.$controller . '.php' )){
			throw new AquaException( AquaException::REST_CONTROLLER_FILE_NOT_FOUND , array( $version, $currentNoun) );
		}
		
		include  $this->currentZoneDir . '/controllers/'.$version.'/'.$controller .  '.php';
		
		if( !class_exists( $controller ) ){
			throw new AquaException( AquaException::REST_CONTROLLER_CLASS_NOT_FOUND, array( $version, $currentNoun) );
		}
		// create noun controller object
		$currentNounObj = new $controller();
		
		$params = array_values( $data );
		if( empty( $data[$currentNoun] ) ){ // url: api/item.json
			// call all method
			$functionName .= '_all';			
			array_pop( $params );
		}
		
		if( !empty( $resourceType )){
			$functionName .= '_'.$resourceType;
		}
		
		if( ! method_exists( $currentNounObj, $functionName ) ){
			throw new AquaException( AquaException::REST_CONTROLLER_METHOD_NOT_FOUND, array( $version, $currentNoun, $functionName) );
		}
		
		if( $this->willCallRESTController( $version, $controller, $functionName, $params) ){
			call_user_func_array( array($currentNounObj, $functionName ), $params );
			$this->didCallRESTController( $version,$controller, $functionName, $params);
		}
		
		
	}
	private function runController( &$urlParts ){
				
		$controller = DEFAULT_CONTROLLER;		
		if( !empty($urlParts ) ){
			if( file_exists( $this->currentZoneDir.'/controllers/'.$urlParts[ 0 ].'.php') ){
				$controller = array_shift( $urlParts );				
			}
		}

		if(! class_exists( $controller )){
			
			throw new AquaException( AquaException::CONTROLLER_NOT_FOUND, $controller );			
		}	
		
		$controllerObj = new $controller();		
		
		$method = 'index';
		if( !empty($urlParts) && method_exists( $controllerObj, $urlParts[0]) ){
			$method = array_shift($urlParts);			
		}
		
		if( method_exists($controllerObj, $method) ){
			$this->currentController = $controllerObj;
			$this->currentControllerClass = $controller;
			
			if( $this->willCallControllerMethod( $controller, $method, $urlParts ) ){
				call_user_func_array( array( $controllerObj, $method ), $urlParts);
				$this->didCallControllerMethod( $controller, $method, $urlParts );
			}
		}else{
			$args = array($controller);
			if( $method == 'index' && !empty( $urlParts ) ){				
				$args[] = $urlParts[0];					
			}
			throw new AquaException( AquaException::CONTROLLER_METHOD_NOT_FOUND, $args );
				
		}
	}
	
	
	private function loadLocale( &$urlParts ){
		if( empty( $urlParts) ){
			return $this->defaultLocale;
		}
		if( in_array( $urlParts[ 0 ], $this->locales ) ){
			return array_shift( $urlParts );
		}
		return $this->defaultLocale;
	}
	private function loadZone( $currentZonePath, &$urlParts, &$zone = 'webroot'){
		if( ! $this->willLoadZone( $zone, $currentZonePath ) )return;
		$this->zoneStack[] = $zone;
		$this->zonePaths[] = $currentZonePath;
				
		// update zone include path		
		set_include_path( $currentZonePath . '/classes' . PATH_SEPARATOR . get_include_path() );
		set_include_path( $currentZonePath . '/models' . PATH_SEPARATOR . get_include_path() );
		set_include_path( $currentZonePath . '/controllers' . PATH_SEPARATOR . get_include_path() );
		
		// include zone inc.php file
		@include $currentZonePath.'/ini.php';
		
		// include locales
		@include( $currentZonePath . '/lang/'.$this->defaultLocale . '.php');
		@include( $currentZonePath . '/lang/'.$this->currentLocale . '.php');
				
		// zone loaded
		$this->didLoadZone( $zone, $currentZonePath );
		
		
		// check recursion termination
		
		if( empty($urlParts)){
			return $currentZonePath;
		}
		
		if( is_dir( $currentZonePath . '/zones/'. $urlParts[0]) ){			
			$this->currentZone = array_shift( $urlParts );
			return $this->loadZone( $currentZonePath . '/zones/'. $this->currentZone, $urlParts, $this->currentZone );
		}
		
		return $currentZonePath;
	}
	
	private function makeURLParts( &$url ){
		$urlParts = array();
		if( !empty( $url ) ){			
			$urlBase = $this->currentDeployment['url_base'];		
			$url = substr( $url, strlen($urlBase) );
			
			$this->currentURL = $url;
			
			// get extension
			$this->urlExtension = substr(strrchr($url, '.'), 1);
			if( !empty( $this->urlExtension ) ){
				$url = substr( $url, 0, - strlen( $this->urlExtension ) -1);
			}
			
			// remove request params
			if( false !== strpos($url,'?') ){
				$url = substr($url,0,strpos($url,'?'));
			}
			
				
			if( !empty( $url ) ){				
				$urlParts = explode('/', trim( $url, '/' ) );				
			}
		}
		return $urlParts;
	}
	private function isAppletURL( &$urlParts ){
		if( empty($urlParts) )return false;
		
		if( APPLET_TRIGGER == $urlParts[ 0 ] ){
			array_shift( $urlParts ); // remove the url trigger from begining
			return true;
		}
		return false;
	}
	
	
	
	private function applyOutputFormatters( $output ){
		
		foreach ( $this->outputFormatters as $formatter ) {			
			$formatter->format( &$output, $this );
		}
	}
	
	public function addOutputFormatter( IOutputFormatter &$formatter ){
		$this->outputFormatters[]= $formatter;
	}
	
	
	private function runApplet( &$urlParts ){
		if( count($urlParts) < 2){ // urls must have applet name and id
			throw new AquaException( AquaException::APPLET_CLASS_NOT_FOUND, implode('/', $urlParts));
		}
		
		$applet = array_shift( $urlParts );
		$id 	= array_shift( $urlParts );
		
		$obj = $this->loadAppletObj($applet, $id);
		$method = 'index';
		if( !empty( $urlParts )){ 
			$method = array_shift( $urlParts );
		}
		
		if( $this->willCallAppletAction( $applet, $id, $method, $urlParts )){
			call_user_func_array( array( $obj, $method ), $urlParts);
			$this->didCallAppletAction( $applet, $id, $method, $urlParts );
			$lastURL = $_SESSION[ SESSION_PREFIX.'LAST_URL'];
			parse_str( parse_url( $lastURL, PHP_URL_QUERY ), $query );
			$appletState = $query[ $id ];
			
			if(  !empty( $appletState) ){
				$lastURL = str_replace( "$id=$appletState",'',$lastURL );				
				$_SESSION[ SESSION_PREFIX.'LAST_URL'] = $lastURL;
			}
			self::redirectLast();
		}
	}
	
	
	public function loadAppletObj( $applet, $id ){
		$appletDir = $this->getAppletDir( $applet ); 
		
				
		set_include_path( $appletDir . '/models' . PATH_SEPARATOR . get_include_path() );
		set_include_path( $appletDir . '/classes' . PATH_SEPARATOR . get_include_path() );
				
		// include the lang file		
		@include( $appletDir . '/lang/'.$this->defaultLocale . '.php');
		@include( $appletDir . '/lang/'.$this->currentLocale . '.php');				
		
		
		// include class file
		include_once( $appletDir.'/'.$applet.'.php' );
		
		if( !class_exists( $applet ) ){
			throw new AquaException( AquaException::APPLET_CLASS_NOT_FOUND, array($applet) );
		}
		
		return new $applet( $id, $appletDir );
	}
	
	/**
	 * Returns the dir that contains the applet class file
	 * 
	 * @param $applet name of the applet
	 * @throws AquaException if not found
	 */
	public function getAppletDir( $applet, $noClass = false ){
		$len = count( $this->zonePaths );
	
		for( $i = $len ; $i--; $i>-1){
			$prefix = $this->zonePaths[ $i ];			
	    	if( file_exists( $prefix.'/applets/'.$applet.'/'.$applet .'.php' ) ){	    		
	    		return  $prefix.'/applets/'.$applet;
	    	}
	    	if( $noClass ){
		    	if( is_dir( $prefix.'/applets/'.$applet ) ){	    		
		    		return  $prefix.'/applets/'.$applet;
		    	}
	    	}
	    }
	    
		if( file_exists( GLOBAL_DIR.'/applets/'.$applet.'/'.$applet.'.php' )){
			return GLOBAL_DIR.'/applets/'.$applet;
		}
		if( $noClass ){
	    	if( is_dir( GLOBAL_DIR.'/applets/'.$applet ) ){	    		
	    		return GLOBAL_DIR.'/applets/'.$applet;
	    	}
    	}
	}
	
	
	public function getLocalizedPath( $type, $file, $ext, $searchRootZoneOnly = false ){		
		if( $searchRootZoneOnly ){
			$prefix = $this->directory.'/webroot';
			
			if( file_exists( $prefix.'/'.$type.'/'.$this->currentLocale.'/'.$file .$ext ) ){	    	
		    		return $prefix.'/'.$type.'/'.$this->currentLocale.'/'.$file .$ext;
	    	}
	    	
			if( file_exists( $prefix.'/'.$type.'/'.$this->defaultLocale.'/'.$file.$ext ) ){	    	
	    		return $prefix.'/'.$type.'/'.$this->defaultLocale.'/'.$file .$ext ;
	    	}
	    		    
			if( file_exists( $prefix.'/'.$type.'/'.$file .$ext ) ){	    	
	    		return $prefix.'/'.$type.'/'.$file .$ext;
			}
			return false;	
		}
		
		$len = count( $this->zonePaths );
		
		for( $i = $len ; $i--; $i>-1){
			$prefix = $this->zonePaths[ $i ];			
	    	
	    	if( file_exists( $prefix.'/'.$type.'/'.$this->currentLocale.'/'.$file .$ext ) ){	    	
	    		return $prefix.'/'.$type.'/'.$this->currentLocale.'/'.$file .$ext;
	    	}
	    	
			if( file_exists( $prefix.'/'.$type.'/'.$this->defaultLocale.'/'.$file.$ext ) ){	    	
	    		return $prefix.'/'.$type.'/'.$this->defaultLocale.'/'.$file .$ext ;
	    	}
			if( file_exists( $prefix.'/'.$type.'/'.$file .$ext ) ){	    	
	    		return $prefix.'/'.$type.'/'.$file .$ext;
			}	    	
	    }
	    
	    return false;
	}
	
	public function getAppletView( $applet, $view, $noClass = false ){
				
		$path = $this->getLocalizedPath( 'applets/'.$applet.'/views', $view , '.php' );
		
		if( $path )return $path;
		
		// check in global folder		
		$prefix = GLOBAL_DIR.'/applets/'.$applet.'/views';			
    	
    	if( file_exists( $prefix.'/'.$this->currentLocale.'/'.$view .'.php' ) ){	    	
    		return $prefix.'/'.$this->currentLocale.'/'.$view .'.php';
    	}
    	
		if( file_exists( $prefix.'/'.$this->defaultLocale.'/'.$view .'.php' ) ){	    	
    		return $prefix.'/'.$this->defaultLocale.'/'.$view .'.php' ;
    	}
		if( file_exists( $prefix.'/'.$view .'.php' ) ){	    	
    		return $prefix.'/'.$view .'.php';
		}
		throw new AquaException( AquaException::APPLET_VIEW_NOT_FOUND, array($applet,$view) );
	}
	
	public function getAppletLayout( $applet, $layout ){
		$path = $this->getLocalizedPath( 'applets/'.$applet.'/layouts', $layout , '.php' );
		
		if( $path )return $path;
		
		// check in global folder		
		$prefix = GLOBAL_DIR.'/applets/'.$applet.'/layouts';			
    	
    	if( file_exists( $prefix.'/'.$this->currentLocale.'/'.$layout .'.php' ) ){	    	
    		return $prefix.'/'.$this->currentLocale.'/'.$layout .'.php';
    	}
    	
		if( file_exists( $prefix.'/'.$this->defaultLocale.'/'.$layout .'.php' ) ){	    	
    		return $prefix.'/'.$this->defaultLocale.'/'.$layout .'.php' ;
    	}
    	
		if( file_exists( $prefix.'/'.$layout .'.php' ) ){	    	
    		return $prefix.'/'.$layout .'.php';
		}
		throw new AquaException( AquaException::APPLET_LAYOUT_NOT_FOUND, array($applet,$layout) );
	}
	
	public function getLayoutPath( $layout, $searchRootZoneOnly = false ){
		$path = $this->getLocalizedPath( 'layouts', $layout , '.php', $searchRootZoneOnly);
		if( $path ){
			return $path;
		}
		throw new AquaException( AquaException::LAYOUT_NOT_FOUND, $layout );
		
	}
	
	function getViewPath( $view ) {		
		$path = $this->getLocalizedPath( 'views', $view , '.php' );
		if( $path ){
			 return $path;
		}
		throw new AquaException( AquaException::VIEW_NOT_FOUND, $view );
	}
	
	public function appletActionURL( $applet, $id, $url, $params = array() ){
		//var_dump(implode('/', $this->zoneStack ));
	$zoneP = empty( $this->zoneStack) == true ? '' :  implode('/', $this->zoneStack ) . '/' ;
		 return $this->baseURL. APPLET_TRIGGER . '/' . $this->currentLocale.'/' . $zoneP .$applet . '/' . $id . '/' . $url . '?'.http_build_query($params);
	}
	
	
	////////// UTILITY /////////////////
	public static function currentLocale(){		
		return self::$instance->currentLocale;
	}
	public static function getDefaultLocale(){
		return self::$instance->defaultLocale;
	}
	public static function getLocales(){
		return self::$instance->locales;
	}
	
	/**
	 * Redirect to some url
	 * 
	 * @param $location relative url or abosolute url, if absolute then last param must be false
	 * @param $locale what should the locale
	 * @param $params any get params to add
	 * @param $addAppRoot default is true. pass false if absolute url is passed as location
	 */
	public static function redirect( $location = '', $locale='', $params=array(), $addAppRoot = true ){
		header('Location:'. self::$instance->url( $location, $locale, $params, $addAppRoot ));
	}
	
	/**
	 * Redirect to the last url
	 */
	public static function redirectLast(){
		header('Location:'.$_SESSION[ SESSION_PREFIX.'LAST_URL']);
	}	
	
	/**
	 * Redirect to some external url
	 */
	public static function redirectExternal( $externalURL ){
		header('Location:'. $externalURL);
	}
	
	/**
	 * Creates an app url. 
	 * Enter description here ...
	 * @param $location relative location. if empty is passed it will return current url
	 * @param $locale what should be the locale
	 * @param $params what should be the get params if any
	 * @param $addAppRoot default true, pass it false if absolute url is passed as location
	 */
	public static function url( $location = '', $locale='', $params=array(), $addAppRoot = true ){
		if( empty($location) ){
			$location = self::$instance->actionURL;
		}
		
		$url = $location;
		
		if( $locale !== false){
			if( empty( $locale) ){
				$locale = self::$instance->currentLocale;
			}
			$url = $locale.'/'.$location;
		}
		
		if( $params !== false ){
			if(empty( $params )){
				$params = $_GET;
			}
			
			if( !empty( $params )){
				$url = $url . '?'.http_build_query($params);
			}
		}
		
		if($addAppRoot){
			$url =  self::$instance->baseURL. $url;
		}		
		return $url;
	}
	
	/**
	 * Creates an anchor tag
	 * 
	 * @param $location any relative or absolute location. This will call App::url(). 
	 * @param $text Text for the anchor
	 * @param $title Title for the anchor
	 * @param $cssClass CSS class name
	 * @param $id ID of the anchor element
	 * @param $locale what should be the locale
	 * @param $params any get params
	 * @param $addAppRoot Should add app root.
	 */
	public static function anchor( $location, $text, $title='', $cssClass='', $id='', $locale='', $params=array(), $addAppRoot = true ){
		$url = self::url( $location, $locale, $params, $addAppRoot);
		echo "<a href='$url' title='$title' class='$cssClass' id='$id'>$text</a>";
	}
	
	/**
	 * Returns the file path or URL location of an asset.
	 * @param $file File name
	 * @param $url If true sends the URL, else file path
	 * @param $type 
	 * @throws AquaException
	 */
	public static function asset( $file, $url = true, $type = ''){
		$path = self::$instance->getLocalizedPath('assets', $file, $type);
		if( $path ){
	    	if( $url ){
	    		return str_replace( self::$instance->directory.'/', self::$instance->baseURL, $path );
	    	}	
	    	return $path;
		}
		throw new AquaException( AquaException::ASSET_NOT_FOUND, array($file) );
	}
	/**
	 * Returns the file path or URL location of a CSS.
	 * @param $file File name
	 * @param $link If true sends the URL, else returns the file content
	 * @param $params To format CSS file content. Keys will be replaced by their values if found.
	 * @param $media Media attribute
	 * @throws AquaException
	 */
	public static function css( $file, $link = true, $params = array(), $media= 'screen, projection' ){
		$path = self::asset( 'css/'.$file, $link );
		
		if( $link ){
			print("\n\t");
			echo '<link href="'.$path.'" rel="stylesheet" type="text/css"  media="'.$media.'"/>';
			print("\n\r");
		}else{
			print("\n\t<style type='text/css'>\n");
			echo str_replace( array_keys( $params)
						, array_values( $params)
						, file_get_contents( $path ) 
					);
			print("\n\t</style>\n\r");
		}
	}
	
	public static function js( $file, $link = true, $params = array() ){
		$path = self::asset( 'js/'.$file, $link );
		
		if( $link ){
			echo "\n\t<script type='text/javascript'  src='"
			,$path
			, "'></script>\n\r";	
		}else{
			print("\n\t<script type='text/javascript' >\n\t\t");
			echo str_replace( array_keys( $params)
						, array_values( $params)
						, file_get_contents( $path ) 
					);
			print("\n\t</script>\n\r");	
		}
	}
	
	public static function jsExternal( $url, $link = true, $params=array()){
		if( $link ){
			echo "\n\t<script type='text/javascript'  src='"
			,$url
			, "'></script>\n\r";	
		}else{
			print("\n\t<script type='text/javascript' >\n\t\t");
			echo str_replace( array_keys( $params)
						, array_values( $params)
						, file_get_contents( $url ) 
					);
			print("\n\t</script>\n\r");	
		}
	}
	
	public static function img( $file, $title='', $cssClass='', $id = ''){
		echo '<img src="'.self::asset( 'images/'.$file, true ).'" class="'.$cssClass.'" title="'.$title.'" id="'.$id.'" />';
	}
	public static function imgURL( $file ){
		return  self::asset( 'images/'.$file, true );
	}
	
	/////////////////// events ////////////////////////
	public function willExecuteURL( &$url ){
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willExecuteURL( &$url ); 
		}
		return $ret;
	}
	
	public function didExecuteURL( &$url, &$content ){
		foreach ($this->delegates as $delegate){
			 $delegate->didExecuteURL( &$url, &$content ); 
		}
	}
		
	public function willCallControllerMethod( &$controller, &$method, &$params ){
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willCallControllerMethod( &$controller, &$method, &$params ); 
		}
		return $ret;
	}
	
	public function didCallControllerMethod( &$controller, &$method, &$params ){
		foreach ($this->delegates as $delegate){
			 $delegate->didCallControllerMethod( &$controller, &$method, &$params ); 
		}
	}
	
	
	public function willCallAppletMethod( &$applet, $id, &$method, &$params ){	
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willCallAppletMethod( &$applet, $id, &$method, &$params ); 
		}
		return $ret;
	}
	
	public function didCallAppletMethod( &$applet, $id, &$method, &$params ){
		foreach ($this->delegates as $delegate){
			 $delegate->didCallAppletMethod(  &$applet, $id, &$method, &$params ); 
		}
	}
	
	public function willCallAppletAction( &$applet, $id, &$method, &$params ){		
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willCallAppletAction(  &$applet, $id, &$method, &$params ); 
		}
		return $ret;
	}
	
	public function didCallAppletAction( &$applet, $id, &$method, &$params ){
		foreach ($this->delegates as $delegate){
			 $delegate->didCallAppletAction( &$applet, $id, &$method, &$params ); 
		}
	}
	
	public function willLoadZone( &$zone, &$path ){	
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willLoadZone(  &$zone, &$path ); 
		}
		return $ret;
	}
	
	public function didLoadZone( &$zone, &$path ){
		foreach ($this->delegates as $delegate){
			 $delegate->didLoadZone( &$zone, &$path ); 
		}
	}
	
	public function willCallRESTController( $version, &$controller, &$method, &$params ){
		$ret = true;
		foreach ($this->delegates as $delegate){
			 $ret = $ret & $delegate->willCallRESTController($version,  &$controller, &$method, &$params ); 
		}
		return $ret;
	}
	
	public function didCallRESTController( $version, &$controller, &$method, &$params ){
		foreach ($this->delegates as $delegate){
			 $delegate->didCallRESTController(  $version, &$controller, &$method, &$params );
		}
	}
	
	///  STATIC WRAPPER METHOD FOR CONFIGURATION ///////	
	public static function getMysqlConnection( $connectionName = 'default' ){
		if( !array_key_exists( $connectionName, self::$instance->currentDeployment['mysqldb']) ){
			throw new AquaException( AquaException::DB_CONF_NOT_FOUND, $connectionName );
		}
		
		$conf = self::$instance->currentDeployment['mysqldb'][$connectionName];
		$host = $conf[ 'host' ];
		$user = $conf[ 'user' ];
		$pass = $conf[ 'password' ];
		$db   = $conf[ 'database' ];
		
		$conn = mysql_connect( $host, $user, $pass );
		if( !$conn ){
			throw new AquaException( AquaException::DB_FAILED_TO_CONNECT, $connectionName);
		}
		if( !empty( $db )){
			mysql_select_db( $db, $conn );
		}
		
		return $conn;
	}
	
	//////// SOME MORE STATIC METHOD WRAPPER FOR USING LIKE App::someMethod(); ///////
	public static function currentView(){
		self::$instance->currentController->includeView();
	}
	public static function baseURL(){
		return self::$instance->baseURL;
	}
	public static function currentZone(){
		return self::$instance->currentZone;
	}
	public static function get( $key ){
		return self::$instance->values[ $key ];
	}
	public static function set( $key, $value ){
		self::$instance->values[ $key ] = $value;
	}
	
	public static function getPath( $path = '' ){
		return self::$instance->directory . '/' . $path;
	}
	
	/**
	 * Use this funciton to add translation to the current locale.
	 * It will overrite any existing values with those of the passed array.
	 * @example 
	 * <code>
	 * // define the translations
	 * 	$lang = array();
	 *  $lang[ 'title' ] = 'Welcome to my app';
	 *  $lang[ 'greeting' ] = 'Hello %s';
	 *  
	 *  // update translation
	 *  App::updateTransalation( $lang );
	 * </code>
	 * @param array $lang
	 */
	public static function updateTransalation( array &$lang ){
		global $__aqua_translations__;
		$__aqua_translations__ = array_merge( $__aqua_translations__, $lang );
	}
	
	////////// viewports  //////////
	private static $viewPorts = array();
	private static $viewPortDelegates = array();
	private static $viewPortStates = array();
	
	const VIEWPORT_STATE_VISIBLE = 1;
	const VIEWPORT_STATE_HIDDEN  = 2;
	
	public static function addViewPort( $viewPortName, IViewPortDelegate $delegate = null ){		
		if( !$delegate ){
			$delegate = new DefaultViewPortDelegate();
		}else if( ! ($delegate instanceof IViewPortDelegate) ){
			throw new AquaException( AquaException::VP_NOT_VALID_DELEGATE, get_class( $delegate ) );
		}
		self::$viewPorts[ $viewPortName ] = array();
		self::$viewPortDelegates[ $viewPortName ] = $delegate;
	}
	
	public static function addToViewPort( $viewPort, IViewPortComponent $comp ){
		if( ! ($comp instanceof IViewPortComponent) ){
			throw new AquaException( AquaException::VP_NOT_VALID_COMPONENT, get_class( $comp ) );
		}
		if( !key_exists( $viewPort, self::$viewPorts ) ){
			self::addViewPort( $viewPort );
		}		
		self::$viewPorts[ $viewPort ][] = $comp;
		
	}
	
	public static function setViewPortDelegate( $viewPort, IViewPortDelegate $delegate ){
		self::$viewPortDelegates[ $viewPort ] = $delegate;
	}
	
	public static function setViewPortState( $viewPort, $state ){
		self::$viewPortStates[ $viewPort ] = $state;
	}
	
	public static function getViewPortState( $viewPort ){
		return self::$viewPortStates[ $viewPort ];
	}
	
	public static function viewportHeader(){		
		foreach( self::$viewPorts as $viewPort=>$components ){
			$delegate 	= self::$viewPortDelegates[ $viewPort ];			
			$delegate->onViewPortHeader( $viewPort, self::$viewPortStates[ $viewPort ], &$components );			
		}
	}
	public static function viewPortRender( $viewPort, $state = false ){
		if( $state !== false){
			self::$viewPortStates[ $viewPort ] = $state;
		}		
		$delegate 	= self::$viewPortDelegates[ $viewPort ];
		$delegate->onViewPortRender( $viewPort, self::$viewPortStates[ $viewPort ], self::$viewPorts[ $viewPort ] );		
	}
	public static function viewPortFooter(){
		foreach( self::$viewPorts as $viewPort=>$components ){
			$delegate 	= self::$viewPortDelegates[ $viewPort ];
			$delegate->onViewPortFooter( $viewPort, self::$viewPortStates[ $viewPort ], &$components );
		}
	}
	
}
?>