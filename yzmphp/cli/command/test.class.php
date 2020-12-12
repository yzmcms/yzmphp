<?php
/**
 * 测试命令
 */

defined('IN_YZMPHP') or exit('Access Denied');
class test extends cli{

	/**
	 * 命令行默认执行的方法
	 * @return    void
	 */
	public function init() {

		self::write('测试命令');
	}	
	
}