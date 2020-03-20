<?php
/**
 * MyDb
 * 采用mysql的方式做的一个数据库的类
 * @package   
 * @author forum
 * @copyright chzcb
 * @version 2009
 * @access public
 */
class MysqlDriver
{
    public $error = null;
    
    protected $_link;
    
    /**
     * MyDb()
     * 
     * @return void
     */
    public function __construct($config)
    {
    	if(!is_array($config)){
    		throw new MException("数据库配置有误");
    	}
    	if(!isset($config['host'])){
    		throw new MException("数据库配置host不存在");
    	}
    	if(!isset($config['user'])){
    		throw new MException("数据库配置user不存在");
    	}
    	if(!isset($config['passwd'])){
    		throw new MException("数据库配置passwd不存在");
    	}
    	if(!isset($config['db'])){
    		throw new MException("数据库配置db不存在");
    	}
    	$this->_link = mysql_connect($config['host'],$config['user'],$config['passwd'],true);
		if(mysql_error()) {
			throw new MException ( mysql_error () );
			return false;
		}
		mysql_select_db ( $config ['db'] ,$this->_link);
		if (isset ( $config ['charset'] ))
			mysql_set_charset ( $config ['charset'], $this->_link );
		else
			mysql_set_charset ( 'utf8', $this->_link );
    	return true;
    }
    
    /**
     * 返回数据库查询结果，作为最大的数组返回
     * fetch_rows()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_rows($str)
    {
        $handle = mysql_query($str,$this->_link);
        if($handle)
        {
            $rows = array();
            $row = mysql_fetch_assoc($handle);
            while($row)
            {
                $rows[] = $row;
                $row = mysql_fetch_assoc($handle);        
            }
            mysql_free_result($handle);
            return $rows;   
        }
        else
        {
            throw new MException('<b>[public function:fetch_rows] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false;  
        }
    }
    public function check($str){
        $handle = mysql_query($str,$this->_link);
        if($handle)
        {
            $rows = array();
            $row = mysql_fetch_assoc($handle);
            mysql_free_result($handle);
            if($row){
            	return true;
            }
        }
        return false;
    }
    /**
     * 返回查询结构的第NUM条的数据，默认是第一条
     * fetch_column()
     * 
     * @param mixed $str
     * @param integer $NUM
     * @return
     */
    public function fetch_one($str,$NUM=0)
    {
        $handle = mysql_query($str,$this->_link);
        if($handle)
        {
            $rows = array();
            $row = mysql_fetch_assoc($handle);
            while($row)
            {
                $rows[] = $row;
                $row = mysql_fetch_assoc($handle);      
            }
            mysql_free_result($handle);
            if(count($rows)>1)
            {
                return $rows[$NUM];            
            }
            else if(count($rows)==1)
            {
                return $rows[0];
            }
            else
            {
                return $rows;        
            } 
        }
        else
        {
            throw new MException('<b>[public function:fetch_one] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false;   
        }
    }
    /**
     * 返回查询结构的第NUM列的数据，默认是第一列
     * fetch_column()
     * 
     * @param mixed $str
     * @param integer $NUM
     * @return
     */
    public function fetch_column($str,$NUM=0)
    {
        $handle = mysql_query($str,$this->_link);
        if($handle)
        {
            $column = array();
            $row = mysql_fetch_assoc($handle);
            while($row)
            {
                $rows[] = $row;
                $cell = array_slice($row,$NUM,$NUM+1);
                if(empty($cell)&&$NUM!=0)
                {
                    throw new MException ('error: the NUM is out your column');
                    return false;
                }
                else if(empty($cell)&&$NUM==0)
                {
                    return $column = array();            
                }
                $row = mysql_fetch_assoc($handle);
                array_push($column,array_shift($cell));
            }
            mysql_free_result($handle);
            return $column;     
        }
        else
        {
            throw new MException('<b>[public function:fetch_column] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false;    
        }
    }
    /**
     * 获得查询语句的第一条，第一列的数值
     * fetch_one_cell()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_one_cell($str)
    {
        $handle = mysql_query($str,$this->_link);
        if($handle)
        {
            $row = mysql_fetch_array($handle);
            if(empty($row))
            {
               return false;
            }
    	 		mysql_free_result($handle);
            $cell = array_shift($row);
            if(is_array($cell))
            {
                return array_shift($cell);        
            }
            else
            {
                return $cell;        
            }      
        }
        else
        {
            throw new MException('<b>[public function:fetch_one_cell] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false;        
        }
        
    }
    /**
     * 返回一个数组，第一列作为key，第二列作为value，其他列有或没有都没有影响
     * fetch_list()
     * 
     * @param mixed $str
     * @return
     */
    public function fetch_list($str)
    {
        $row_1 = self::fetch_column($str);
        $row_2 = self::fetch_column($str,1);
        $number = count($row_1);
        for($i=0;$i<$number;$i++)
        {
            $list[$row_1[$i]] = $row_2[$i];
        }
        return $list;
    }
    /**
     * 
     * 
     * @param mixed $str
     * @return
     */
    public function query($str)
    {
    	 $handler = mysql_query($str,$this->_link);
    	 if($handler)
    	 	mysql_free_result($handler);
       if(mysql_error())
        {
            throw new MException('<b>[public function:delete] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false; 
        }
        else
        {
            return true;
        }
    }
    
    /**
     * 
     * update()
     * 
     * @param mixed $str
     * @return
     */
    public function update($str)
    {
        $handler = mysql_query($str,$this->_link);
        if(mysql_error())
        {
            throw new MException('<b>[public function:update] - error:</b>'.mysql_error().', query='.$str);
 				self::$error = mysql_error();
            return false;           
        }
        else
        {
            return true;
        }
    }
    /**
     * 插入数据库的函数
     * 
     * @param mixed $str
     * @return
     */
    public function insert($str)
    {
        if(mysql_query($str,$this->_link))
        {
            return mysql_insert_id();
        }
        else
        {
            throw new MException('<b>[public function:insert] - error:</b>'.mysql_error().', query='.$str);
            self::$error = mysql_error();
            return false;        
        }
    }

    public function fetch_fields($table_name){
        $result = mysql_query("desc $table_name",$this->_link);
        $rows = array();
        while($row = mysql_fetch_assoc($result))
            $rows[] = $row;
        if($result)
            mysql_free_result($result);
        return $rows;
    }

    public function getFields($tablename){
    	$result = mysql_query("SELECT * FROM $tablename limit 1",$this->_link);
    	$fields = mysql_num_fields($result);
    	$ret = array();
    	for ($i = 0;$i<$fields;$i++){
    		$type = mysql_field_type($result, $i);
       		$name  = mysql_field_name($result, $i);
       		$ret[$name] = $type;
    	}
    	return $ret;
    }
    
 	public function getFlags($tablename){
    	$result = mysql_query("SELECT * FROM $tablename limit 1",$this->_link);
    	$fields = mysql_num_fields($result);
    	$ret = array();
    	for ($i = 0;$i<$fields;$i++){
       		$name  = mysql_field_name($result, $i);
       		$flags = mysql_field_flags($result, $i);
       		$ret[$name] = $flags;
    	}
    	return $ret;
    }

    public function getLink(){
 	    return $this->_link;
    }

    public function prepare($str){
        throw new MException("当前DB驱动不支持Prepare方法");
    }
    
}
	
