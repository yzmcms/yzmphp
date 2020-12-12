<?php
/**
 * 命令帮助
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-12-10
 */

defined('IN_YZMPHP') or exit('Access Denied');
class help extends cli{
	

	/**
	 * 打印命令帮助
	 * @return    void
	 */
	public function init() {
		self::version();
		self::write('All Command:', 'magenta');
		$command = self::get_command();
		foreach ($command as $key=>$value) {
			self::write($key, 'yellow', 0);
			foreach ($value as $k=>$v) {
				self::write($k, 'green', 2, 0);
				self::write($v, '', 3);
			}
		}
	}

	
	/**
	 * 打印version
	 * @return    string
	 */
	private function version() {
		self::write('YZMPHP '.YZMPHP_VERSION.' (PHP V'.PHP_VERSION.')', 'yellow');
		self::write('');
	}
	
}