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
?>
<html>
<title>PHP AQUA : Program Error Occured!</title>
<body>
<div style='border:0px solid red;line-height: 2em;padding:20px'>
	<div style="color:red;text-align: center"><?php echo $type ?></div>
	<div style="font-family: courier;">
	<hr/>
		<table>
			<tr>
				<td>Message</td><td> : <?php echo $message; ?></td>
			</tr>
			<tr>
				<td>File</td><td> : <?php echo $file; ?></td>
			</tr>
			<tr>
				<td>Line</td><td> : <?php echo $line; ?></td>
			</tr>			
		</table>
		<hr/>
		<?php
		
	//	debug_print_backtrace();		
		?>
	</div>
	
</div>
</body>
</html>