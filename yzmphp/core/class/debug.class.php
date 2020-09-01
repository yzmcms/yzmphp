<?php
/**
 * debug.class.php   debug类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-07-06
 */
 
class debug {

	public static $info = array();
	public static $sqls = array();
	public static $request = array();
	public static $stoptime; 
	public static $msg = array(
				E_WARNING => '错误警告',
				E_NOTICE => '错误提醒',
				E_STRICT => '编码标准化警告',
				E_USER_ERROR => '自定义错误',
				E_USER_WARNING => '自定义警告',
				E_USER_NOTICE => '自定义提醒',
				'Unkown ' => '未知错误'
	        );
	

	/**
	 *在脚本结束处调用获取脚本结束时间的微秒值
	 */
	public static function stop(){
		self::$stoptime= microtime(true);  
	}

	
	/**
	 *返回同一脚本中两次获取时间的差值
	 */
	public static function spent(){
		return round((self::$stoptime - SYS_START_TIME) , 4);  //计算后以4舍5入保留4位返回
	}

	
	/**
	 * 错误 handler
	 */
	public static function catcher($errno, $errstr, $errfile, $errline){
		if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
			if(!isset(self::$msg[$errno])) 
				$errno='Unkown';

			if($errno==E_NOTICE || $errno==E_USER_NOTICE)
				$color="#151515";
			else
				$color="red";

			$mess = '<span style="color:'.$color.'">';
			$mess .= '<b>'.self::$msg[$errno].'</b> [文件 '.$errfile.' 中,第 '.$errline.' 行] ：';
			$mess .= $errstr;
			$mess .= '</span>'; 		
			self::addmsg($mess);			
		}else{
			if($errno==8) return '';
			error_log('<?php exit;?> Error : '.date('Y-m-d H:i:s').' | '.$errno.' | '.str_pad($errstr,30).' | '.$errfile.' | '.$errline."\r\n", 3, YZMPHP_PATH.'cache/error_log.php');
		}
	}


	/**
	 * 致命错误 fatalerror
	 */
	public static function fatalerror(){
		if ($e = error_get_last()) {
            switch($e['type']){
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:  
                ob_end_clean();
                if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
                	application::fatalerror($e['message'], $e['file'].' on line '.$e['line'], 1);	
           		}else{
           			error_log('<?php exit;?> FatalError : '.date('Y-m-d H:i:s').' message:'.$e['message'].', file:'.$e['file'].', line:'.$e['line']."\r\n", 3, YZMPHP_PATH.'cache/error_log.php');
           			application::halt('error message has been saved.', 500);
           		}
                break;
            }
        }
	}
	
	
	/**
	 * 捕获异常
	 * @param	object	$exception
	 */ 
	public static function exception($exception){
		if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
			$mess = '<span style="color:red">';
			$mess .= '<b>系统异常</b> [文件 '.$exception->getFile().' 中,第 '.$exception->getLine().' 行] ：';
			$mess .= $exception->getMessage();
			$mess .= '</span>'; 		
			self::addmsg($mess);
		}else{
			error_log('<?php exit;?> ExceptionError : '.date('Y-m-d H:i:s').' | '.$exception->getMessage().' | '.$exception->getFile().' | '.$exception->getLine()."\r\n", 3, YZMPHP_PATH.'cache/error_log.php');
		}
		showmsg($exception->getMessage(), 'stop');
	}

	
	/**
	 * 添加调试消息
	 * @param	string	$msg	调试消息字符串
	 * @param	int	    $type	消息的类型
	 */
	public static function addmsg($msg, $type=0) {
		switch($type){
			case 0:
				self::$info[] = $msg;
				break;
			case 1:
				self::$sqls[] = htmlspecialchars($msg).';';
				break;
			case 2:
				self::$request[] = $msg;
				break;
		}
	}


	/**
	 * 获取debug信息
	 */
	public static function get_debug() {
		return array(
			'info' => self::$info,
			'sqls' => self::$sqls,
			'request' => self::$request
		);
	}
	
	
	/**
	 * 输出调试消息
	 */
	public static function message(){
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'debug.tpl');	
	}
}
