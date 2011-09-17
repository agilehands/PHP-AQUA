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
 * @author Shaikh Sonny Aman <agilehands@gmail.com>
 * 	
 * #######################################################################
 */

/**
 * This is an experimental class, not yet ready for production use.
 * Yet to test!
 */
namespace aqua;

use aqua\exception\AquaException;
use aqua\App;
class MysqlAgent
{
	protected $host		= '';
	protected $user		= '';
	protected $password	= '';
	protected $database	= '';
	protected $link=null;
	
	private $lastResult = null;
	private $lastSql = null;
	public $hasError = false;
	public $errorMsg = '';
	
	function __construct(  ){
	}
	function connect( $connectionName = 'default' ){
		$this->link = App::getMysqlConnection( $connectionName );	
	}
	function execute($sql, array $params = array())
	{	
		$this->lastResult = null;
		$this->hasError = false;
		$this->lastSql = null;
		
		if( count($params) > 0 ){
			$sql = vsprintf( $sql, $params );
		}

		$this->lastSql = $sql;
		$res =  mysql_query($sql);
		
		if( $res === false ){
			$this->hasError = true;
			throw new AquaException( AquaException::DB_ERROR, array( mysql_error( $this->link ) ));
		}
		
		$this->lastResult = $res;
		return $res;
	}
	
	function all($sql, array $params = array()){
		$res = $this->execute( $sql, $params );
		if( !$res ){
			return false;
		}
		
		$data = array();
		while( $row = mysql_fetch_assoc( $res ) ){
			$data[] = $row;
		}
		return $data;
	}
	
	public function first(){
		$res = $this->execute( $sql, $params );
		if( !$res ){
			return false;
		}
		
		$data = array();
		while( $row = mysql_fetch_assoc( $res ) ){
			$data[] = $row;
		}
		if( count($data) > 0 ){
			return $data[0];
		}
		
		return null;
	}
	
	public function getRowCount(){
		if( $this->hasError )return false;
				
		return mysql_num_rows( $this->lastResult );
	}
	
	public function getLastSQL(){
		return $this->lastSql;
	}
	
	function getConnection(){
		return $this->link;
	}
	
	/**
	 * Checks if the value is unique in the user table
	 *
	 * @param  $col The column in the user tabls
	 * @param  $val The value to be checked
	 */
	public function isUnique( $col, $val, $table ){
		if( $this->hasError )return false;
		$col = mysql_real_escape_string($col);
		$val = mysql_real_escape_string($val);
		$table = mysql_real_escape_string($table);
		$sql = "select * from $table where $col='$val';";
		$res = $this->execute($sql);
		if($this->getRowCount()>0){
			return false;
		}
		return true;
	}
	
	// tables must have primary key named as id[tablename] i.e. if the table name is
	// is students its PK must be: idstudents
	function update($table,$id,$data){
		if( $this->hasError )return false;
		
		$vals = '';
		foreach($data as $key=>$val){
			$vals .= "$key=$val,";
		}
		$vals = substr($vals,0,-1);
		$sql = "update $table set $vals where id$table=$id";
		$res = $this->execute($sql);
	}
	
	function addNew( $table, $data ){
		if( $this->hasError )return false;
		
		$vals = '';
		$cols = '';
		foreach($data as $key=>$val){
			$vals .= "$val,";
			$cols .= "$key,";
		}
		$vals = substr($vals,0,-1);
		$cols = substr($cols,0,-1);
		
		$sql = "insert into $table($cols) values($vals)";
		
		$res = $this->execute($sql);
		if($this->hasError)return false;
		
		return mysql_insert_id($this->link);
	}
	function getInsertId(){
		return mysql_insert_id($this->link);
	}
	
	static function clean($val){
		$val = mysql_real_escape_string($val,$this->link);
		return $val;
	}	
}
?>
