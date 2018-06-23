<?php
/**
 * cli.class.php cli命令行处理类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-04-16 
 */
 
class cli {
	
	public static $version = '1.0';
	
	/**
	 *  开始处理cli命令
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	public static function start($cli) {
		array_shift($cli);
		$command = isset($cli[0]) ? $cli[0] : '';
		if($command){
			$commands = self::get_command();
			if(!in_array($command, $commands)) self::halt($command.' command Not existent.');
			self::check_command($command);
			array_shift($cli);
			$command::exec($cli);
		}else{
			self::welcome();
		}
	}	


	/**
	 *  输出欢迎提示
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	public static function welcome() {
		echo 'Welcome to use YZMPHP '.self::$version;
	}
	
	
	/**
	 *  获取所有命令
	 *
	 * @return    array
	 */
	public static function get_command() {
		return require(CLI_PATH.'command'.DIRECTORY_SEPARATOR.'command.php');
	}


	/**
	 *  检查命令类是否存在
	 *
	 * @return    void
	 */
	public static function check_command($command) {
		if (!is_file(CLI_PATH.'command'.DIRECTORY_SEPARATOR.$command.EXT)) self::halt($command.EXT.' file does not exist.');
		require(CLI_PATH.'command'.DIRECTORY_SEPARATOR.$command.EXT);
		if(!class_exists($command)) self::halt($command.' class does not exist.');
		return true;
	}	

	
	/**
	 *  输出错误提示
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	public static function halt($msg) {
		echo 'Error:'.$msg;
		exit();
	}
}