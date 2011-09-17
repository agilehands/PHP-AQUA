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

use aqua\DefaultAppDelegate;
use aqua\App;

include('../../aqua/Aqua.php');


$deployments = array();
$deployments['localhost']=array();
$deployments['localhost']['url_base'] = '/phpaqua/apps/mysite/';
$deployments['localhost']['mysqldb']['default'] = array(
							'host'=>'localhost'
							,'user'=>'root'
							,'password'=>''
						       	,'database'=>'phpaqua'
							);													
	
$deployments['www.mysite.com'] = array();
$deployments['www.mysite.com']['url_base'] = '/';
$deployments['www.mysite.com']['mysqldb']['default'] = array(
							     'host'=>'localhost'
							     ,'user'=>'root'
							     ,'password'=>'pass'
							     ,'database'=>'phpaqua'
							     );

$app = 	new \aqua\App(
		      /* site directory */	str_replace('\\', '/', realpath(dirname(__FILE__))) /* required */
		      /* deployments */		, $deployments    /* required */		
		      /* default locale */ 	, 'en'	          /* optional */
		      /* all locale */		, array('en','bn')/* optional */	
		      );

// now run the App
$app->run();	
	
?>