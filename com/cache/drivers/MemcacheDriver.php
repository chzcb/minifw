<?php

class MemcacheDriver{
	
	const TAGS_KEY = 'fw::memcache_tags_array_1';
	
	private $_servers = array();
	
	private $_memcache;
	
	private $_tags;
	
	private $_tags_changed = false;
	
	private $_flags;
	
	public function __construct($config){
		$this->_servers = isset($config['servers'])?$config['servers']:(isset($config['server'])?array($config['server']):array(array('127.0.0.1',11211)));
		if(class_exists('Memcache')){
			$this->_memcache = new Memcache();
			foreach($this->_servers as $ms){
  				$this->_memcache->addServer($ms[0], $ms[1]);
			}
			$this->_tags = $this->_memcache->get(self::TAGS_KEY);

			if(!is_array($this->_tags)){
				$this->_tags = array();
				$this->_tags_changed = true;
			}
			if(isset($config['compress'])){
				$this->_flags = $config['compress']?MEMCACHE_COMPRESSED:false;
			}
		}
		else
		{
			throw new MException("请检查PHP配置，安装Memcache扩展");
		}
	}
	
	public function __destruct(){
		if($this->_tags_changed === true){
			$this->_memcache->set(self::TAGS_KEY,$this->_tags,$this->_flags);
			$this->_tags_changed = false;
		}
	}
	
	public function set($id,$value,$tags=null,$expire=0){
		if(!empty($tags)){
			$this->_tags_changed = true;
			foreach ($tags as $tag){
			    if(!$this->_tags[$tag]){
			        $this->_tags[$tag] = array();
                }
				array_push($this->_tags[$tag], $id);
			}
		}
		if($expire !== 0){
			$expire += time();
		}
		return $this->_memcache->set($id,$value,$this->_flags,$expire);
	}
	
	public function get($name){
		return (($ret = $this->_memcache->get($name)) === false)?null:$ret;
	}
	
	public function find($tag){
		if(isset($this->_tags[$tag]) && $results = $this->_memcache->get($this->_tags[$tag])){
			return $results;
		}
		else
		{
			return array();
		}
	}
	
	public function delete($id,$tag=FALSE){
		$this->_tags_changed = true;
		if($id === true){
			if($status = $this->_memcache->flush()){
				$this->_tags = array();
				sleep(1);
			}
			return $status;
		}
		elseif($tag === true){
			if(isset($this->_tags[$id])){
				foreach($this->_tags[$id] as $_id){
					$this->_memcache->delete($_id);
				}
				unset($this->_tags[$id]);
			}
			return true;
		}
		else
		{
			foreach($this->_tags as $tag => $_ids){
				if(isset($this->_tags[$tag][$id])){
					unset($this->_tags[$tag][$id]);
				}
			}
			return $this->_memcache->delete($id);
		}
	}
	
	public function delete_expired(){
		$this->_tags_changed = true;
		foreach($this->_tags as $tag => $_ids){
			foreach ($_ids as $id){
				if(!$this->_memcache->get($id)){
					unset($this->_tags[$tag][$id]);
				}
			}
			if(empty($this->_tags[$tag])){
				unset($this->_tags[$tag]);
			}
		}
		return true;
	}
	
}

?>