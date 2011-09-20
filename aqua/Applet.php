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
 * 	Feel free to change and use but don't need to share the code.
 *
 * 					
 * 	
 * ################   http://www.phpaqua.com   ###############
 */

	/**	
	 * 		!!!!!! THIS DOCUMENTATION IS NOT MAINTAINED AND MAY BE OBSOLET !!!!!	
	 * 		FOR LATEST DOCUMENT PLZ REFER TO SITE: http://www.phpaqua.com
	 * 
	 *  
	 * Applets are like modules. They are used to implement some functionalities
	 * and develop the application in a more modular fashion.
	 * 	 
	 */
namespace aqua;
	use aqua\exception\AquaException;

	class Applet extends Controller{
		
		
		/**
		 * ID of the applet, used in making the url
		 */
		private $_id;
		
		
		private $_dir;
		
		public $_appletZone;		
		
		
		/**
		 * @param $name The name of the applet
		 */
		public function __construct( $id, $dir ){			
			parent::__construct();			
			$this->_isApplet = true;
			$this->_className = get_class($this);			
			$this->_id = $id;
			$this->_dir =  $dir;
		}		
		
		
			
		public function call( $method, $params = array()){
			
			$_GET[ $this->_id ] =  base64_encode( json_encode( array('action'=>$method, 'params'=>$params)) );			
			
			return App::$instance->url('','',$_GET);
		}
		public function anchor( $method, $text, $title='', $cssClass='', $params = array() ){
			
			$_GET[ $this->_id ] =  base64_encode( json_encode( array('action'=>$method, 'params'=>$params)) );
			echo '<a href="'.App::$instance->url('','',$_GET).'" title="'.$title.'" class="'.$cssClass.'" >'.$text.'</a>';
		}
		
		public function action( $url = 'index', $params= array() ){
			if(empty($url))$url = 'index';
			return App::$instance->appletActionURL( $this->_className, $this->_id, $url, $params);
		}
		
		function state($key){
			if(func_num_args() > 1){// want to set
				$this->session($this->_id.$key, func_get_arg(1));
			}else{				
				return $this->session($this->_id.$key);
			}
		}		
		
		function index(){
			
		}
		/**
		 * Use this function to include some arbitary view organized
		 * in applets
		 * Applet::view('someview',array('some'=>'params'));
		 * 
		 * No applet class is required! Just place a view in an applet
		 * folder and use it!
		 */
		public static function view( $applet, $view, $params = array() ){
			$path = App::$instance->getAppletView( $applet, $view, true );
			extract($params);
			
			// include the lang file
			$appletDir = App::$instance->getAppletDir( $applet, true ); 		
			
			@include( $appletDir . '/lang/'.App::getDefaultLocale() . '.php');
			@include( $appletDir . '/lang/'.App::currentLocale() . '.php');		
		
			include( $path );
		}
		
		public static function render( $applet, $id, $view='', $args=array() ){
					
			$obj = App::$instance->loadAppletObj( $applet, $id );			
			if( !empty( $view ) ){			
				$obj->show( $view, $args );
				return;
			}			
			$action = $obj->state('__lastAction__');
			if( empty( $action) ) $action = 'index';
			
			$params = $obj->state('__lastActionParams__');
			if( empty( $params) ) $params = $args;
			
			if( $obj->state( '__executed__') == true ){
				$obj->state( '__executed__', false );
			}else{
			
				$data = @$_GET[ $id ]; // safe to suppress, checking array key exist takes time
				//		
				if( !empty( $data ) ){
					$data = json_decode( base64_decode( $data ), true);
					$action = $data['action'];
					$params = $data['params'];
				}
				
				if( !method_exists( $obj, $action )){
					$action = 'index';
				}
			}
			
			if( App::$instance->willCallAppletMethod( $applet, $id, $action, $params )){
				call_user_func_array( array( $obj, $action ), $params);
				$obj->state('__lastAction__', $action );
				$obj->state('__lastActionParams__', $params );
				App::$instance->didCallAppletMethod( $applet, $id, $action, $params );
			}			
		}
		
		/**
		 * In Applet mode, it is not possible to show any view directly because
		 * site is redirected to last url.
		 * 
		 * Call this method to ensure that this method will be called after redirection
		 */
		protected function execute( $method, $params ){
			$this->state( '__executed__', true );
			$this->state( '__lastAction__', $method );
			$this->state( '__lastActionParams__', $params );
		}
		
		public static function asset( $applet, $file, $ext, $url = true){
			$path = App::$instance->getLocalizedPath( 'applets/'.$applet.'/assets', $file ,$ext );
						
			if( !$path ){
				// check in global folder		
				$prefix = GLOBAL_DIR.'/applets/'.$applet.'/assets';				
		    	if( file_exists( $prefix.'/'.App::$instance->currentLocale.'/'.$file.$ext ) ){	    	
		    		$path =  $prefix.'/'.App::$instance->currentLocale.'/'.$file.$ext;
		    	}
				if( file_exists( $prefix.'/'.App::$instance->defaultLocale.'/'.$file.$ext ) ){	    	
		    		$path = $prefix.'/'.App::$instance->defaultLocale.'/'.$file.$ext;
		    	}		    	
				
		    	if( file_exists( $prefix.'/'.$file.$ext ) ){	    	
		    		$path =  $prefix.'/'.$file.$ext;
				}
				
				if( !$path ){				
					throw new AquaException( AquaException::APPLET_GLOBAL_ASSET_LINK_ASKED, array( $applet, $file ) );			
				}				
			}
			if( $path){
				if( $url ){
					return str_replace( App::$instance->directory.'/', App::$instance->baseURL, $path );
				}
				return $path;
			}
			
			throw new AquaException( AquaException::APPLET_ASSET_NOT_FOUND, array( $applet, $file ) );			
		}
		public static function css( $applet, $file, $link = true, $params = array() ){
			$path = self::asset( $applet, 'css/'.$file, '', $link );
			
			if( $link ){
				print("\n\t");
				echo '<link href="'.$path.'" rel="stylesheet" type="text/css" />';
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
		
		public static function js( $applet, $file, $link = true, $params = array() ){
			$path = self::asset( $applet, 'js/'.$file, '', $link );
			
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
		
		public static function imgURL( $applet, $file ){
			return self::asset( $applet, 'images/'.$file, '', true );
		}
		
		public static function img( $applet, $file, $title='', $cssClass='', $id = ''){			
			echo '<img src="'.self::asset( $applet, 'images/'.$file, '', true ).'" class="'.$cssClass.'" title="'.$title.'" id="'.$id.'" />';
		}
	
		function once($key){
			if(func_num_args() > 1){// want to set
				$this->session($this->_id.$key, func_get_arg(1));
			}else{				
				$ret = $this->session($this->_id.$key);
				unset($_SESSION[SESSION_PREFIX.$this->_id.$key]);
				return $ret;
			}
		}
		
		
	}// end class
	
?>