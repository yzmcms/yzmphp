<?php
class common{
	
	public function __construct() {
		self::check_admin();
	}



	/**
	 * 判断用户是否已经登陆
	 */
	public function check_admin() {
		echo '<span style="color:red">这是后台登录验证的方法，在这里可以编写您的代码！</span><br><br>';
	}


	
	/**
	 * 加载后台模板
	 * @param string $file 文件名
	 * @param string $m 模型名
	 */
	public static function admin_tpl($file, $m = '') {
		$m = empty($m) ? ROUTE_M : $m;
		if(empty($m)) return false;
		return APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$file.'.html';
	}	
}