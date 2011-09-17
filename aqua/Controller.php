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
/**
 * 
 * This is the base class for all Controllers and Applets.
 * 
 * This class contains many utility functions
 * @author Aman
 *
 */
class Controller{	
	public $_className;
	protected $_isApplet = false;
	protected $layoutPath = false;
	protected 	$layoutParams = array();
	protected	$viewPath;		
	protected $viewParams = array();
		
	public function __construct(){
		$this->_isApplet = false;
	}
	/**
	 * Sets/Gets a session key
	 */
	function session($key){			
		if(func_num_args() > 1){// want to set
			$_SESSION[SESSION_PREFIX.$key] = func_get_arg(1);
		}else{
			$ret = @$_SESSION[SESSION_PREFIX.$key];
			return $ret;
		}
	}
	
	function get( $key, $sanitize = false ){
		return $this->_safe( @$_GET[$key], $sanitize );
	}
	
	function post( $key, $sanitize = false ){
		return $this->_safe( @$_POST[$key], $sanitize );
	}
	/**
	 * Should be only used inside a Layout.
	 * @return unknown_type
	 */
	public function includeView(){					
		extract($this->viewParams);			
		include( $this->viewPath );
	}
		
	
	public function addLayoutParam($key,$value){
		$this->layoutParams[$key] = $value;
	}	

	/**
	 * Stores a variable in session. Removes the value from session on first use
	 * 
	 * If two parameter is passed, it sets the value, if one passed it removes the key and return the value;
	 *
	 * @param $key
	 */
	function once($key){
		if(func_num_args() > 1){// want to set
			$_SESSION[SESSION_PREFIX.$key] = func_get_arg(1);
		}else{
			$ret = @$_SESSION[SESSION_PREFIX.$key];
			unset($_SESSION[SESSION_PREFIX.$key]);
			return $ret;
		}
	}
	
	public function show( $view, $params=array() ){
			if( $this->_isApplet){
				$this->viewPath =  App::$instance->getAppletView( $this->_className, $view );
			}else{
				$this->viewPath = App::$instance->getViewPath( $view );
			}
			
			if( $this->layoutPath ){
				extract($this->layoutParams);
				$this->viewParams = $params;	
				include $this->layoutPath;
			}else{
				$this->includeView();
			}
	}
	
	protected function setLayout( $layout, $isWebrootLayout = false ){
		if( $layout == false ){
			$this->layoutPath = false;
			return;
		}
		
		if( $this->_isApplet ){
			$this->layoutPath = App::$instance->getAppletLayout( $this->_className, $layout, $isWebrootLayout );
		}else{
			$this->layoutPath =  App::$instance->getLayoutPath( $layout, $isWebrootLayout );
		}
		
	}
	 
	/**
	 * Cleans a value by sanitizing and stripping tags 
	 */
	function _safe( $val, $sanitize=false ){
		if(empty($val))return $val;
		
		if($sanitize){
			switch ($sanitize){
				case 'email':
					if(!filter_var($val, FILTER_VALIDATE_EMAIL)){
						$val = false;
					}
					break;
				
				case 'plain': // no tag, no esp char
					
					$val  = filter_var($val, FILTER_SANITIZE_STRIPPED | FILTER_SANITIZE_SPECIAL_CHARS);
					
					break;
					
				case 'int':
					if(!filter_var($val, FILTER_VALIDATE_INT)){
						$val = false;
					}
					
					break;
				
				case 'url':
					if(!filter_var($val, FILTER_VALIDATE_URL)){
						$val = false;
					}
					$val = filter_var($val, FILTER_SANITIZE_URL);	
					break;
					
				case 'mysql':
					$val = MysqlAgent::clean($val);
					break;
			}
		}
	
		return $val;
	}
	/**
	 * Sanitizes an input
	 * @param $input the inp
	 * @param $link
	 */
	function sanitize($input){
	
		if(is_array($input)){
	
			foreach($input as $k=>$i){
				$output[$k]= $this->sanitize($i);
			}
		}
		else{
				
			if(get_magic_quotes_gpc()){
				$input=stripslashes($input);
			}
				
			$output=mysql_real_escape_string($input);
		}
		return $output;
	}
	function cookie( $key, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null){
		if( func_num_args() > 1 ){// set
			setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
			return;
		}
		
		if(isset($_COOKIE['__AQUA__'.$name])){
			return stripslashes($_COOKIE['__AQUA__'.$name]);
		}
		return '';
	}
	
	function isPosted(){	
		return ( strtolower( $_SERVER['REQUEST_METHOD']) == 'post');
	}
}

?>