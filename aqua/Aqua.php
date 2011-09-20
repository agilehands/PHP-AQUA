<?
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
 * FOR THE CONTRIBUTORS:
 * 
 * Code Duplication: Some code duplication are intentional to reduce few cpu cycle
 * Not using class/namespace: Though ofte debatable, but again to reduce few cpu cycle
 * 
 * The idea is we, including you ( :) ), will be doing the NASTY job to make the framework
 * as light as possible while adding new features.
 * 
 * @author PHP AQUA  <phpaqua@gmail.com>
 * 	
 * #######################################################################
 */
function __autoload($class_name) {
	$class_name = str_replace('\\', '/', $class_name);
	$include_path = get_include_path(); 
    $include_path_tokens = explode(':', $include_path);     
    foreach($include_path_tokens as $prefix){    	
    	//echo $prefix.'/'.$class_name . '.php<br/>';
    	if( file_exists( $prefix.'/'.$class_name . '.php') ){
    		include_once $prefix.'/'.$class_name . '.php';
    		
    	
    		//echo 'found!';
    		break;
    	}    	
    	//throw new \Exception("Class file not found: <b>$class_name.php</b>");
    }
    
    //echo '<hr/>';
}
define('AQUA_DIR', 				str_replace('\\', '/', realpath(dirname(__FILE__))) );
define('GLOBAL_DIR',  			AQUA_DIR . '/global' );
define('SESSION_PREFIX',  		'__PHP_AQUA__' );
define('DEFAULT_CONTROLLER',  	'_default' );
define('APPLET_TRIGGER',  		'qax' );

//define('WEB_ROOT', 'webroot');// sacrificing this for little speed

// add global include path
set_include_path( AQUA_DIR . PATH_SEPARATOR . get_include_path() );
set_include_path( dirname(AQUA_DIR) . PATH_SEPARATOR . get_include_path() );
set_include_path( GLOBAL_DIR . '/models' . PATH_SEPARATOR . get_include_path() );
set_include_path( GLOBAL_DIR . '/classes' . PATH_SEPARATOR . get_include_path() );

$httpErrorCodes = array();

// include core files
include 'MysqlAgent.php';
//include 'SimpleRelationalRecord.php';
include 'Controller.php';

include 'Applet.php';

include 'exceptions/AquaException.php';
include 'httperror/IHTTPErrorHandler.php';
include 'httperror/DefaultHTTPErrorHandler.php';
include 'IOutputFormatter.php';
include 'IExceptionHandler.php';
include 'DefaultExceptionHandler.php';
include 'AbstractAppDelegate.php';
include 'DefaultAppDelegate.php';
include 'DefaultFormatter.php';
include 'App.php';

use aqua\exception\AquaException;
use aqua\App;

$siteObj = null;
function setAppInstance( App &$site ){
	global $siteObj;	
	$siteObj = $site;
	
}

function AquaErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    
    switch ($errno) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $errors = "Notice";
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $errors = "Warning";
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $errors = "Fatal Error";
            break;
        default:
            $errors = "Unknown";
            break;
        }

    if (ini_get("display_errors")){
    	throw new AquaException( AquaException::PROGRAM_ERROR, array('type'=>$errors, 'message'=>$errstr, 'file'=>$errfile, 'line'=>$errline) );
    }
        
    if (ini_get('log_errors'))
        error_log(sprintf("PHP %s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline));
        
    return true;
}

/**
 * Get the translation,
 */
$__aqua_translations__ = array();
function _t($key,$args = array()){
	global $__aqua_translations__;
	if(!isset($__aqua_translations__[$key])){
		return $key;
	}else{
		if( !empty($args) ){
			return vsprintf($__aqua_translations__[$key] , $args);
		}
		return $__aqua_translations__[$key];
	}
}

?>