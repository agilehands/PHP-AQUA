<?
/**
 *
 * ######################################################################
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

use aqua\MysqlAgent;
class SimpleRelationalRecord
{
	private $b_isExists = false;
	private $db_fields	= array();
	private $db_prefix	= null;
	private $fields		= array();
	private $prefix		= null;	
	private $table		= null;
	private $db_pk		= null;
	
	private $dbagent	= null;	
	private $relations 	= array();
	
	//later
	private $db_field_type = null;
	
	private $conn = null;
	private $b_multiple = false;
		
	
	/**
	 * Adds information from a relational table
	 * 
	 * @param table The table name
	 * @param fk 	The foreign key field in the above table
	 * @param pk	The primary key for the base table
	 */
	function addRelation($table,$fk='',$pk='')
	{
		$fk = strlen($fk)>0 ? $fk : $this->db_pk;
		$pk = strlen($fk)>0 ? $pk : $this->db_pk;
		
		// Get the relational data;
		$rs = $this->dbagent->execute('select * from ' . $table . ' where ' . $fk . ' = \''.$this->$pk . '\';');				
		
		// Will store the values
		$rows = array();		
		
		// Populate the rows
		while ($row = mysql_fetch_assoc($rs)) 
		{
			$rows[]=$row;
		}
		
		// Set teh rows associated to the table name;
		$this->relations[$table]=$rows;
	}
	
	
	/**
	 * Retrieves a column value/s from a relational table which is
	 * already added. If more than relational table contains columns
	 * with same name, the function will take the first one.
	 * 
	 * If the relational table has more than one entry for that key,
	 * the function will return an array, otherwise it will return
	 * only the value.
	 * 
	 * @param key The column name 
	 */	
	private function get_from_relation($key)
	{		
		// Wills store the returning value/s
		$tmp_vals = array();
		
		foreach($this->relations as $table=>$rows)
		{
			// set it if the key is found
			$flag = false;
			
			foreach($rows as $row)
			{
				if(array_key_exists($key,$row))
				{
					$flag = true;
					$tmp_vals[] = $row[$key];					
				}
			}
			
			if($flag===true)
			{
				break;
			}
		}
		
		return sizeof($tmp_vals)==1 ? $tmp_vals[0] : $tmp_vals;		
	}
	
	/**
	 *  Check if the model row exists in the table
	 */
	function isExists()
	{
		return $this->b_isExists;
	}
	
	
	/**
	 * Loads the value from the database without overriting
	 * any value set by the source array like $_POST,$_GET etc 
	 */
	function load()
	{		
		$this->b_isExists = false;
		$flag=false;
		$sql = 'select * from '.$this->table . ' where ';
		foreach($this->db_fields as $col=>$val)
		{
			if(isset($val) && strlen($val)>0)
			{
				$sql .= $col .'=\'' . $val . '\' and ';	
				$flag = true;
			}
		}
		
		//remove last comma;
		$sql = substr($sql, 0, -4);	
		//=echo $sql;
		if($flag===false)
		{
			return false;	
		}
		
		$res = $this->dbagent->execute($sql);
		$this->conn = $res;
		if(mysql_num_rows($res)==1)
		{
			$this->b_isExists=true;			
			$row=mysql_fetch_array($res);
			
			foreach($row as $col=>$val)
			{
				
				if(isset($val) && strlen($val)>0)
				{
					//Do not override existing values, as they are
					//latest from data
					if(array_key_exists($col,$this->db_fields) && strlen($this->db_fields[$col])<1)
					{
						//  echo $col.'='.$val.'<br/>';
						$this->db_fields[$col]=$val;
					}
				}
			}
			
			return true;	
		}
		else{
			return false;	
		}		
	}
	
	public function __construct( $dbConf = 'default' ){
		$this->dbagent = new MysqlAgent();
		$this->dbagent->connect( $dbConf );		
	}	
	
	/**
	 * @param table 		Table name
	 * @param source The 	source array. It can be $_POST,$_GET or any array
	 * @param pk_col_name 	Primary key for the table
	 * @param prefix The 	prefix for the table column to be found in the aray
	 * @param $db_prefix 	If any database prefix is used 
	 */	
	function init($table,$source=array(),$pk_col_name=null,$prefix=null,$db_prefix=null)
	{ 
		$this->table	 = $table;
		$this->db_pk	 = is_null($pk_col_name) ? 'id'.$table	:	$pk_col_name;		
		$this->prefix 	 = is_null($prefix)		?	''	:	$prefix;
		$this->db_prefix = is_null($db_prefix)	?	''	:	$db_prefix;		
		
		//Load table column names as accesible property
		$this->init_db_fields($table);
		
		//If no source array is provided, load from $_REQUEST
		if(sizeof($source)<1)
		{
			$source=$_REQUEST;	
		}
		
		//$this->setId($source[$pk_col_name]);
		
		foreach($source as $key=>$val)
		{
			//echo $key .'='.$val.'<br/>';
			$this->$key=$val;
		}
		
		//Load rest of the data from database;
		$this->load();	

	}
	
	function setId($id)
	{
		//load the table columns
		$this->db_fields[$this->db_pk]=$id;
		
		$sql='select * from '. $this->table . ' where '.$this->db_pk . '=\''.$id . '\'';
		//echo $sql . '<br/>';
		$res = $this->dbagent->execute($sql);
		while($row = mysql_fetch_assoc($res))
		{
			// echo "<br/>";
			 foreach($row as $col=>$val)
			 {
			 	$this->db_fields[$col]=$val;
			 }
		}		
	}
	
	function getId()
	{
		echo $this->db_fields[$this->db_pk];			
	}	
	
	function update($col_vals=array())
	{		
		print_r($col_vals);//exit;
		$sql = ' update ' .$this->table . ' set ';
		foreach($col_vals as $col=>$val)
		{
			if(array_key_exists($col,$this->db_fields))
			{
				$sql .= $col .'=\'' . $val . '\',';	
			}
		}
		
		//remove last comma;
		$sql = substr($sql, 0, -1);
		
		$sql .=' where '. $this->db_pk.'=\''.$this->db_fields[$this->db_pk].'\'';
		//echo $sql;exit;
		$this->dbagent->execute($sql);
	}
	
	function delete($cond)
	{		
		print_r($col_vals);//exit;
		$sql = ' delete from ' .$this->table . ' where ' . $cond;		
		//echo $sql;exit;
		$this->dbagent->execute($sql);
	}
	
	
	// Inserts or replaces all columns only to the model.
	// Saving relational model is comming next.	
	function save()
	{
		
		
		$tmp='';
		$tmp1='';
		$flag = false;
		//print_r(array_keys($this->db_fields));
		//exit;
		foreach($this->db_fields as $col=>$val)
		{
			if(isset($val) && strlen($val)>0)
			{
				//$sql .= $col .'=\'' . $val . '\' and ';	
				$flag = true;
				$tmp .= $col.','; 
				$tmp1 .= '\''.$val.'\',';
			}
		}
                
		if($flag==true)
		{
                    
			$tmp= substr($tmp, 0, -1);	
			$tmp1= substr($tmp1, 0, -1);	
			$sql = ' REPLACE INTO ' .$this->table . ' ('.$tmp .') values ('.$tmp1.')';
			//$sql .= ' values('. implode(array_values($this->db_fields),',') .')';	
				//echo '<br>'.$sql;
                                 
			$this->dbagent->execute($sql);
		}
               
	}
	
	
	/**
	 * @param table retrieves the table name 
	 */
	function init_db_fields($table)
	{		
		//Get the table column names, should use $dal->get_table_columns($table)
		$res  = $this->dbagent->execute('desc '.$table);
		
		while ($line = mysql_fetch_assoc($res))
		{			
			//print_r( $line) . '<br/>';echo '--'. $line['Field'];exit;			
			$this->db_fields[$line['Field']]='';
		}
		//print_r($this->db_fields);
	}
	
	function __get($key)
	{	
		//print_r($this->db_fields);
		return array_key_exists($key,$this->db_fields)
			?	 $this->db_fields[$key]
			:	(array_key_exists($key,$this->fields)
				?	 $this->fields[$key]
				:	 $this->get_from_relation($key));
	}
	function __set($key, $val)
	{		
		array_key_exists($key,$this->db_fields)
			?	$this->db_fields[$key]=$val
			:	$this->fields[$key]=$val;
	}
	
	public function next(){
		$row=mysql_fetch_array($this->conn);
		
		if($row == false)return false;
			
		foreach($row as $col=>$val)
		{
			if(isset($val) && strlen($val)>0)
			{
				// Override in this case
				if(array_key_exists($col,$this->db_fields) /*&& strlen($this->db_fields[$col])<1*/)
				{
					  //echo '-------',$col.'='.$val.'-----<br/>';
					$this->db_fields[$col]=$val;
				}
			}
		}
		//die();
		return true;
	}
}

//////////////////////////////////////////////////////////

///usage:////////////////////
/*	
$am = new SimpleRelationalRecord();
$am->init('users',array('url'=>'ss'),'id');


echo 'name = '.$am->username . '<br/>';
$am->username = 'Aman';
$am->save();
echo 'name = '.$am->username . '<br/>';

$am->username = 'Hasin';
$am->save();
echo 'name = '.$am->username . '<br/>';
//$am->name='amanasdf';
echo '<hr/>';
$am->addRelation('messages','userid','id');
print_r($am->message);
*/
?>
