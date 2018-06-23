<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class index extends common {

	public function init() {
		// $admin = D('admin');
		// $res = $admin->select();
		// $admin->lastsql();
		// P($res);
		include $this->admin_tpl('index');
	}
	
	
	public function test() {
		echo '您现在访问的是admin模块下的index控制器的test方法！';
	}
	

}