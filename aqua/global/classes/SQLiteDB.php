<?
/**
 * This class is taken from http://code.jenseng.com/db/
 *
 */

namespace aqua;
  define(DB_BOTH,0);
  define(DB_ASSOC,1);
  define(DB_NUM,2);
  
  class DB{
    var $handle;
    function DB($handle){
      $this->handle = $handle;
    }
    function fetch_assoc($result){
      return $this->fetch_array($result,DB_ASSOC);
    }
    function fetch_row($result){
      return $this->fetch_array($result,DB_NUM);
    }
  }
  
  class SQLiteDB extends DB{
    var $error;
    
    function SQLiteDB(){
      $this->error = '';
      $args = func_get_args();
      set_error_handler(array(&$this, 'catcherror'));
      switch(func_num_args()){
      case 1:
        $handle = sqlite_open($args[0]);
        break;
      case 2:
        $handle = sqlite_open($args[0], $args[1]);
        break;
      default:
        $handle = sqlite_open($args[0], $args[1], $args[2]);
        break;
      }
      restore_error_handler();
      if($this->error){
        return null;
      }
      $this->DB($handle);
    }
    function query($sql){
      $this->error = '';
      if(strtolower(substr(ltrim($sql),0,5))=='alter'){
        $queryparts = preg_split("/[\s]+/",$sql,4,PREG_SPLIT_NO_EMPTY);
        $tablename = $queryparts[2];
        $alterdefs = $queryparts[3];
        if(strtolower($queryparts[1]) != 'table' || $queryparts[2] == '')
          $this->error = 'near "'.$queryparts[0] . '": syntax error';
        else{
          set_error_handler(array(&$this, 'catcherror'));
          $result = $this->altertable($tablename,$alterdefs);
          restore_error_handler();
        }
      }
      else{
        set_error_handler(array(&$this, 'catcherror'));
        $result = sqlite_query($this->handle,$sql);
        restore_error_handler();
        if($this->error){
          return null;
        }
      }
      return $result;
    }
    function altertable($table,$alterdefs){
      if($alterdefs != ''){
        $result = sqlite_query($this->handle,"SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '".$table."' ORDER BY type DESC");
        if(sqlite_num_rows($result)>0){
          $row = sqlite_fetch_array($result); //table sql
          $tmpname = 't'.time();
          $origsql = trim(preg_replace("/[\s]+/"," ",str_replace(",",", ",preg_replace("/[\(]/","( ",$row['sql'],1))));
          $createtemptableSQL = 'CREATE TEMPORARY '.substr(trim(preg_replace("'".$table."'",$tmpname,$origsql,1)),6);
          $createindexsql = array();
          $i = 0;
          $defs = preg_split("/[,]+/",$alterdefs,-1,PREG_SPLIT_NO_EMPTY);
          $prevword = $table;
          $oldcols = preg_split("/[,]+/",substr(trim($createtemptableSQL),strpos(trim($createtemptableSQL),'(')+1),-1,PREG_SPLIT_NO_EMPTY);
          $newcols = array();
          for($i=0;$i<sizeof($oldcols);$i++){
            $colparts = preg_split("/[\s]+/",$oldcols[$i],-1,PREG_SPLIT_NO_EMPTY);
            $oldcols[$i] = $colparts[0];
            $newcols[$colparts[0]] = $colparts[0];
          }
          $newcolumns = '';
          $oldcolumns = '';
          reset($newcols);
          while(list($key,$val) = each($newcols)){
            $newcolumns .= ($newcolumns?', ':'').$val;
            $oldcolumns .= ($oldcolumns?', ':'').$key;
          }
          $copytotempsql = 'INSERT INTO '.$tmpname.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$table;
          $dropoldsql = 'DROP TABLE '.$table;
          $createtesttableSQL = $createtemptableSQL;
          foreach($defs as $def){
            $defparts = preg_split("/[\s]+/",$def,-1,PREG_SPLIT_NO_EMPTY);
            $action = strtolower($defparts[0]);
            switch($action){
            case 'add':
              if(sizeof($defparts) <= 2){
                trigger_error('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').'": syntax error',E_USER_WARNING);
                return false;
              }
              $createtesttableSQL = substr($createtesttableSQL,0,strlen($createtesttableSQL)-1).',';
              for($i=1;$i<sizeof($defparts);$i++)
                $createtesttableSQL.=' '.$defparts[$i];
              $createtesttableSQL.=')';
              break;
            case 'change':
              if(sizeof($defparts) <= 3){
                trigger_error('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').($defparts[2]?' '.$defparts[2]:'').'": syntax error',E_USER_WARNING);
                return false;
              }
              if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' ')){
                if($newcols[$defparts[1]] != $defparts[1]){
                  trigger_error('unknown column "'.$defparts[1].'" in "'.$table.'"',E_USER_WARNING);
                  return false;
                }
                $newcols[$defparts[1]] = $defparts[2];
                $nextcommapos = strpos($createtesttableSQL,',',$severpos);
                $insertval = '';
                for($i=2;$i<sizeof($defparts);$i++)
                  $insertval.=' '.$defparts[$i];
                if($nextcommapos)
                  $createtesttableSQL = substr($createtesttableSQL,0,$severpos).$insertval.substr($createtesttableSQL,$nextcommapos);
                else
                  $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1)).$insertval.')';
              }
              else{
                trigger_error('unknown column "'.$defparts[1].'" in "'.$table.'"',E_USER_WARNING);
                return false;
              }
              break;
            case 'drop':
              if(sizeof($defparts) < 2){
                trigger_error('near "'.$defparts[0].($defparts[1]?' '.$defparts[1]:'').'": syntax error',E_USER_WARNING);
                return false;
              }
              if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' ')){
                $nextcommapos = strpos($createtesttableSQL,',',$severpos);
                if($nextcommapos)
                  $createtesttableSQL = substr($createtesttableSQL,0,$severpos).substr($createtesttableSQL,$nextcommapos + 1);
                else
                  $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1) - 1).')';
                unset($newcols[$defparts[1]]);
              }
              else{
                trigger_error('unknown column "'.$defparts[1].'" in "'.$table.'"',E_USER_WARNING);
                return false;
              }
              break;
            default:
              trigger_error('near "'.$prevword.'": syntax error',E_USER_WARNING);
              return false;
            }
            $prevword = $defparts[sizeof($defparts)-1];
          }
          
          
          //this block of code generates a test table simply to verify that the columns specifed are valid in an sql statement
          //this ensures that no reserved words are used as columns, for example
          sqlite_query($this->handle,$createtesttableSQL);
          if($this->error){
            return false;
          }
          $droptempsql = 'DROP TABLE '.$tmpname;
          sqlite_query($this->handle,$droptempsql);
          //end block
          
          
          $createnewtableSQL = 'CREATE '.substr(trim(preg_replace("'".$tmpname."'",$table,$createtesttableSQL,1)),17);
          $newcolumns = '';
          $oldcolumns = '';
          reset($newcols);
          while(list($key,$val) = each($newcols)){
            $newcolumns .= ($newcolumns?', ':'').$val;
            $oldcolumns .= ($oldcolumns?', ':'').$key;
          }
          $copytonewsql = 'INSERT INTO '.$table.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$tmpname;
          
          
          sqlite_query($this->handle,$createtemptableSQL); //create temp table
          sqlite_query($this->handle,$copytotempsql); //copy to table
          sqlite_query($this->handle,$dropoldsql); //drop old table
          
          sqlite_query($this->handle,$createnewtableSQL); //recreate original table
          sqlite_query($this->handle,$copytonewsql); //copy back to original table
          sqlite_query($this->handle,$droptempsql); //drop temp table
        }
        else{
          trigger_error('no such table: '.$table,E_USER_WARNING);
          return false;
        }
        return true;
      }
    }
    function fetch_array($result){
      $this->error = '';
      if(func_num_args() > 1){
        $args = func_get_args(); 
        $arg = $args[1];
        switch($arg){
        case DB_ASSOC:
          return sqlite_fetch_array($result,SQLITE_ASSOC);
          break;
        case DB_NUM:
          return sqlite_fetch_array($result,SQLITE_NUM);
          break;
        default:
          return sqlite_fetch_array($result,SQLITE_BOTH);
          break;
        }
      }
      else
        return sqlite_fetch_array($result);
    }
    function close(){
      $this->error = '';
      sqlite_close($this->handle);
      return true;
    }
    function num_rows($result){
      $this->error = '';
      return sqlite_num_rows($result);
    }
    function catcherror($errorcode,$message){
      if(substr($message,0,16)=='sqlite_query(): '){
        $this->error = substr($message,16);
      }
      else{
        echo $message.'<hr>todo: optimize error handling...';
        exit;
      }
    }
    function seek($result,$rownum){
      return sqlite_seek($result,$rownum);
    }
    function insert_id(){
      return sqlite_last_insert_rowid($this->handle);
    }
  }
?>
