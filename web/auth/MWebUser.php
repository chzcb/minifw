<?php
class MWebUser extends MComponent
{
	public $loginUrl = '/admin/login';
	
	public function checkAccess(){
		$session = Fw::app()->getSession();
		if($session->islogin === true){
			return true;
		}
		return false;
	}
	
	public function setLogin(){
		$session = Fw::app()->getSession();
		$session->islogin = true;
	}
	
	public function setLogout(){
		$session = Fw::app()->getSession();
		$session->islogin = false;
	}
}