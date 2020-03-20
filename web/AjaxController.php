<?php

class AjaxController extends MController {
	
	public function run() {
		$actionname = MRoute::getAction ( $this->defaultAction );
		$actionname = strtolower ( $actionname );
		$first = substr ( $actionname, 0, 1 );
		$main = substr ( $actionname, 1 );
		$actionname = 'action' . strtoupper ( $first ) . $main;
		$this->_action = $actionname;
		$this->$actionname ();
		if (file_exists ( $this->layoutPath . '/' . $this->layout . '.tpl' )) {
			ob_start ();
			include $this->layoutPath . '/' . $this->layout . '.tpl';
			$content = ob_get_contents ();
			ob_clean ();
			preg_match_all ( "#(href|src|action)=([^\s]+)#", $content, $matches );
			$length = count ( $matches [0] );
			$repacearr = array ();
			for($i = 0; $i < $length; $i ++) {
				$url = $matches [2] [$i];
				if (strpos ( $url, "http" ) === false && substr ( $url, 1, 1 ) != '/') {
					$repacearr [$url] = substr ( $url, 0, 1 ) . MRoute::getBaseUrl () . '/' . substr ( $url, 1 );
				}
			}
			echo strtr ( $content, $repacearr );
			flush ();
		}
	}

}

?>