<?php

class WebController extends MController
{
	public $layout = 'main';
	
	public $defaultAction = 'index';
	
	public $_action;
	
	public $name = '';
	
	public $_cache = false;
	
	public function run(){
        Fw::app()->type = 'WEB';
		if($this->_cache){
			$cache = MCache::instance();
			$content = $cache->get(urlencode(MRoute::getRequestUrl()));
		}
		if(empty($content) || $this->_cache == false){

		    //如果没有Action
			if(!($actionname = MRoute::getAction())){
			    //如果当前
			    $actionname = $this->ctrlname?$this->ctrlname:$this->defaultAction;
            }
			$actionname = strtolower($actionname);
			$first = substr($actionname,0,1);
			$main = substr($actionname,1);
			$actionname = 'action'.strtoupper($first).$main;
			$this->_action = $actionname;
			if(method_exists($this, 'prepare')) {
				$this->prepare();
			}
			$args = array_slice(MRoute::getPaths(),2);
			try {
			    //用于清理系统报错
			    ob_start();
                $ret = call_user_func_array(array($this,$actionname),$args);
            } catch (MWebException $e){
                $error = array('error_code'=>$e->error_code,'error_info'=>$e->error_info);
            }
            if(isset($error)){
                ob_end_clean();
            }
            else if($this->output)
            {
                echo ob_get_clean();
                return;
            }
			//如果当前有结果返回，就当做JSON接口处理
			if(MRoute::isAjax()){
                if(isset($error))
                {
                    echo json_encode($error);return;
                }
                echo json_encode($ret);
                return;
            }
			if(file_exists($this->layoutPath.'/'.$this->layout.'.tpl')){
				ob_start();
				if($this->tpl_type == 'smarty'){
                    require_once FW_PATH.'/libs/smarty/Smarty.class.php';
                    $smarty = new Smarty();
                    $smarty->compile_dir = './.smarty/compile_dir/';

                    $smarty->cache_dir = './.smarty/cache_dir/';

                    if(!file_exists($smarty->compile_dir)){
                        mkdir($smarty->compile_dir,0777,true);
                    }
                    if(!file_exists($smarty->cache_dir)){
                        mkdir($smarty->cache_dir,0777,true);
                    }


                    //设置默认变量
                    $smarty->assign('webRoot',MRoute::getBaseUrl());
                    foreach($this->_data as $key=>$value)
                    {
                        $smarty->assign($key,$value);
                    }
                    $smarty->display($this->layoutPath.'/'.$this->layout.'.tpl');
                }
                else
                {
                    include $this->layoutPath.'/'.$this->layout.'.tpl';
                }

				$content = ob_get_contents();
				ob_clean();
//				preg_match_all("#(href|src|action)=([^\s]+)#", $content, $matches);
//				$length = count($matches[0]);
//				$repacearr = array();
//				for($i=0;$i<$length;$i++){
//					$url = $matches[2][$i];
//					if(strpos($url, "http")===false && substr($url, 1,1) != '/' && strpos($url,'javascript') == -1){
//						$repacearr[$url] = substr($url, 0,1).MRoute::getBaseUrl().'/'.substr($url, 1);
//					}
//				}
//				$content = strtr($content,$repacearr);
				echo $content;
				flush();
			} else {
			    if(is_string($ret))
			        echo $ret;
			    else
			        echo json_encode($ret);
            }
			if($this->_cache)//缓存保持10秒钟
				$cache->set(urlencode(MRoute::getRequestUrl()), $content,null,10);
		}
		else
			echo $content;
	}
}