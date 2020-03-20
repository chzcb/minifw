<?php

class MSession extends MComponent {

	public function __construct(){
        $this->initialize();
	}

	function initialize($data=array()){
        session_start();
        $sessionkey = 'MinifwSession';
        if(!isset($_SESSION[$sessionkey])){
            $_SESSION[$sessionkey] = array();
        }

        $this->_data = &$_SESSION[$sessionkey];
        $this->tempid = session_id();
        if(isset($_SERVER['HTTP_USER_AGENT']))
            $this->browers = $_SERVER['HTTP_USER_AGENT'];
        if($_SERVER['REQUEST_TIME'])
            $this->request_time = $_SERVER['REQUEST_TIME'];
        foreach ($data as $key=>$value){
            $this->_data[$key] = $value;
        }
    }
	
	public function registerId($session_id,$data=array()){
		$this->tempid = 0;
		session_regenerate_id(true);
		session_commit();
        $this->id = $session_id;
		session_id($this->id);
		$this->initialize($data);
	}
}

?>