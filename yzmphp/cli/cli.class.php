<?php
/**
 * cli.class.php cli命令行处理类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-05-16 
 */
 
class cli {

	protected static $parameter;
	protected static $colors = array(
		'black'   => array('set' => 30, 'unset' => 39),
		'red'     => array('set' => 31, 'unset' => 39),
		'green'   => array('set' => 32, 'unset' => 39),
		'yellow'  => array('set' => 33, 'unset' => 39),
		'blue'    => array('set' => 34, 'unset' => 39),
		'magenta' => array('set' => 35, 'unset' => 39),
		'cyan'    => array('set' => 36, 'unset' => 39),
		'white'   => array('set' => 37, 'unset' => 39),
	);

	protected static $backgrounds = array(
	    'black'   => array('set' => 40, 'unset' => 49),
	    'red'     => array('set' => 41, 'unset' => 49),
	    'green'   => array('set' => 42, 'unset' => 49),
	    'yellow'  => array('set' => 43, 'unset' => 49),
	    'blue'    => array('set' => 44, 'unset' => 49),
	    'magenta' => array('set' => 45, 'unset' => 49),
	    'cyan'    => array('set' => 46, 'unset' => 49),
	    'white'   => array('set' => 47, 'unset' => 49),
	);

	
	/**
	 * 开始处理cli命令
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	public static function start($cli) {
		array_shift($cli);
		$command = isset($cli[0]) ? $cli[0] : 'help';
		$commands = self::get_command();
		if(!isset($commands[$command])) self::halt($command.' command Not existent.');
		self::check_command($command);
		self::exec($command, $cli);
	}	

	
	
	/**
	 * 获取所有命令
	 *
	 * @return    array
	 */
	protected static function get_command() {
		return require(CLI_PATH.'command'.DIRECTORY_SEPARATOR.'command.php');
	}


	/**
	 * 输出带样式的文字
	 * @param  string $text       
	 * @param  string $color      
	 * @param  string $background 
	 * @return string             
	 */
	protected static function output($text, $color = '', $background = ''){
		if(isset(self::$colors[$color])){
			$set_code[] = self::$colors[$color]['set'];
			$unset_code[] = self::$colors[$color]['unset'];
		}
		if(isset(self::$backgrounds[$background])){
			$set_code[] = self::$backgrounds[$background]['set'];
			$unset_code[] = self::$backgrounds[$background]['unset'];
		}

		if(!isset($set_code)) return $text;

	    return sprintf("\033[%sm%s\033[%sm", join(';', $set_code), $text, join(';', $unset_code));
	}


	/**
	 * 获取命令行参数
	 * example test -a=1 -b=2
	 * @return array             
	 */
	protected static function get() { 
		$_ARG = array(); 
		foreach (self::$parameter as $arg) { 
			if (preg_match('/-([^=]+)=(.*)/',$arg,$reg)) { 
				$_ARG[$reg[1]] = $reg[2]; 
			}
		} 
		return $_ARG; 
	} 


	/**
	 * 输出错误提示
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	protected static function halt($msg) {
		fwrite(STDERR, self::output('Error: '.$msg, 'white', 'red'));
		write_log('Cli Error: '.$msg);
		exit();
	}


	/**
	 * 输出提示
	 * @param  string  $msg 
	 * @param  string  $color 
	 * @param  integer $empty 
	 * @param  integer $newline 
	 */
	protected static function write($msg, $color='', $empty=0, $newline=1) {
		$str = str_repeat(' ', $empty).$msg;
		if($color) $str = self::output($str, $color);
		if($newline) $str .= PHP_EOL;
		fwrite(STDOUT, $str);
	}



	/**
	 * 执行命令
	 *
	 * @param     string  $command
	 * @param     array  $parameter
	 * @return    void
	 */
	private static function exec($command, $parameter) {
		array_shift($parameter);
		self::$parameter = $parameter;
		$controller = new $command;
		$method = isset($parameter[0]) ? $parameter[0] : 'init';
		if(!method_exists($controller, $method)) self::halt($method.' method does not exist.');
		call_user_func(array($controller, $method));
	}


	/**
	 * 检查命令类是否存在
	 *
	 * @return    void
	 */
	private static function check_command($command) {
		if (!is_file(CLI_PATH.'command'.DIRECTORY_SEPARATOR.$command.EXT)) self::halt($command.EXT.' file does not exist.');
		require(CLI_PATH.'command'.DIRECTORY_SEPARATOR.$command.EXT);
		if(!class_exists($command)) self::halt($command.' class does not exist.');
		return true;
	}	

}