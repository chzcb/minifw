<?php
/**
 * 采用mysqli的方式做的一个数据库的类
 * @package
 * @author chzcb
 * @copyright chzcb
 * @version 2009
 * @access public
 */
class MysqliDriver
{
    public $error = null;

    protected $db;

    /**
     *
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

        //放弃直接链接，因为不能设置超时时间，会影响
        // $this->db = new mysqli($config['host'],$config['user'],$config['passwd'],$config ['db']);

        $this->db = new mysqli();
        $this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT,5);
        $this->db->connect($config['host'],$config['user'],$config['passwd'],$config ['db']);
        $this->db->autocommit(true);
        if($this->db->connect_error) {
            throw new MException ( $this->db->connect_error );
            return false;
        }

        if (isset ( $config ['charset'] ))
            $this->db->set_charset( $config ['charset']);
        else
            $this->db->set_charset ( 'utf8' );
        return true;
    }

    public function __destruct() {
        $this->db->close();
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
        $result = $this->db->query($str);
        if($result)
        {
            $rownum = $result->num_rows;
            $rows = array();
            while($rownum-->0){
                $rows[] = $result->fetch_assoc();
            }
            $result->free();
            return $rows;
        }
        else
        {
            throw new MException('<b>[public function:fetch_rows] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
            return false;
        }
    }
    public function check($str){
        $result = $this->db->query($str);
        if($result)
        {
            $result->close();
            if($result->num_rows){
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
        $result = $this->db->query($str);
        if($result)
        {
            $rows = array();
            $row = $result->fetch_assoc();
            while($row)
            {
                $rows[] = $row;
                $row = $result->fetch_assoc();
            }
            $result->close();
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
            throw new MException('<b>[public function:fetch_one] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
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
        $result = $this->db->query($str);
        if($result)
        {
            $column = array();
            $row = $result->fetch_row();
            while($row)
            {
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
                $row = $result->fetch_row();
                array_push($column,array_shift($cell));
            }
            $result->close();
            return $column;
        }
        else
        {
            throw new MException('<b>[public function:fetch_column] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
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
        $result = $this->db->query($str);
        if($result)
        {
            $row = $result->fetch_array();
            if(empty($row))
            {
                return false;
            }
            $result->close();
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
            throw new MException('<b>[public function:fetch_one_cell] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
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

    public function prepare($str){
        try
        {
            return $this->db->prepare($str);
        }
        catch(Exception $e){
            throw new MException('<b>[public function:prepare] - error:</b>'.$this->db->error.', query='.$str);
            return false;
        }
    }

    public function call_rows($str){
        if($this->db->multi_query($str))
        {
            $rows_list = array();
            do
            {
                $rows = array();
                if($result = $this->db->store_result()){
                    $rownum = $result->num_rows;
                    while($rownum-->0){
                        $rows[] = $result->fetch_assoc();
                    }
                    $rows_list[] = $rows;
                    $result->close();
                    if (!$this->db->more_results()) {
                        break;
                    }
                } else {
                    break;
                }
            }
            while ($this->db->next_result());
            return $rows_list[0];
        }
        else
        {
            throw new MException('<b>[public function:fetch_rows] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
            return false;
        }
    }

    public function fetch_fields($table_name){
        $result = $this->db->query("desc ".$table_name);
        if($result)
        {
            $row = $result->fetch_assoc();
            while($row)
            {
                $rows[] = $row;
                $row = $result->fetch_assoc();
            }
            $result->close();
            return $rows;
        }
        else
        {
            throw new MException('<b>[public function:fetch_fields] - error:</b>'.$this->db->error.', query=desc '.$table_name);
            self::$error = $this->db->error;
            return false;
        }
    }

    /**
     *
     *
     * @param mixed $str
     * @return
     */
    public function query($str)
    {
        $result = $this->db->query($str);

        if(!$result)
        {
            throw new MException('<b>[public function:query] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
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
        $result = $this->db->query($str);
        if($result == false)
        {
            throw new MException('<b>[public function:update] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
            return false;
        }
        else
        {
            return $this->db->affected_rows;
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
        $result = $this->db->query($str);
        if($result)
        {
            return $this->db->insert_id;
        }
        else
        {
            throw new MException('<b>[public function:insert] - error:</b>'.$this->db->error.', query='.$str);
            self::$error = $this->db->error;
            return false;
        }
    }

    public function getLink(){
        return $this->db;
    }


}

