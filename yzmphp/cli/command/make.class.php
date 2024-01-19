<?php
/**
 * make命令处理
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-08-18
 *
 * 	使用说明：
 * 	php yzm make module test  ---创建test模块
 * 	php yzm make module test test ---创建test模块的同时并在该模块下创建test控制器
 * 	php yzm make controller test mytest ---在test模块下创建mytest的控制器
 * 	php yzm make model test mytest ---在test模块下创建mytest的模型
 * 	php yzm make job test_job ---创建test_job队列文件
 *
 */

defined('IN_YZMPHP') or exit('Access Denied');
class make extends cli{

	/**
	 * 命令行默认执行的方法
	 * @return    void
	 */
	public function init() {

	}
	

    /**
     * 创建模块
     */	
	public function module() {

		$parameter = self::$parameter;
		$module = $parameter[1];
		if(is_dir(APP_PATH.$module)) self::halt($module.' module already existed.');
		$r = @mkdir(APP_PATH.$module, 0755);
		if(!$r) self::halt('No authority.');
		array_map('mkdir', array(APP_PATH.$module.DIRECTORY_SEPARATOR.'common', APP_PATH.$module.DIRECTORY_SEPARATOR.'controller', APP_PATH.$module.DIRECTORY_SEPARATOR.'model', APP_PATH.$module.DIRECTORY_SEPARATOR.'view'));
		array_map(
			array(__CLASS__, 'empty_index'), 
			array(APP_PATH.$module.DIRECTORY_SEPARATOR, 
				APP_PATH.$module.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR, 
				APP_PATH.$module.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR, 
				APP_PATH.$module.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR, 
				APP_PATH.$module.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR
			)
		);
		mkdir(APP_PATH.$module.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'language');
		self::empty_index(APP_PATH.$module.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR);
		if(isset($parameter[2])) self::controller($parameter);
		self::write('Module created successfully.', 'green');
	}
	

    /**
     * 创建控制器
     */		
	public function controller() {
		
		$parameter = self::$parameter;
		if(!isset($parameter[1])) self::halt('module can\'t be empty.');
		if(!isset($parameter[2])) self::halt('controller can\'t be empty.');
		if(!is_dir(APP_PATH.$parameter[1].DIRECTORY_SEPARATOR.'controller')) self::halt($parameter[1].' module not existent.');		
		$controller_file = APP_PATH.$parameter[1].DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$parameter[2].'.class.php';
		if(is_file($controller_file)) self::halt('controller already exists.');
		
		$file = CLI_PATH.'tpl'.DIRECTORY_SEPARATOR.'controller.class.tpl';
		if(!is_file($file)) self::halt('controller file not existent.');
        $data = file_get_contents($file);
        $content = str_replace(array('{MODULE}', '{CONTROLLER}'), array($parameter[1], $parameter[2]), $data);
        $len = @file_put_contents($controller_file, $content);
		$len ? self::write('Controller created successfully.', 'green') : self::write('Controller created failed.', 'red');
	}
	

    /**
     * 创建模型
     */		
	public function model() {

		$parameter = self::$parameter;
		if(!isset($parameter[1])) self::halt('module can\'t be empty.');
		if(!isset($parameter[2])) self::halt('model can\'t be empty.');
		if(!is_dir(APP_PATH.$parameter[1].DIRECTORY_SEPARATOR.'model')) self::halt($parameter[1].' module not existent.');
		$model_file = APP_PATH.$parameter[1].DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$parameter[2].'.class.php';
		if(is_file($model_file)) self::halt('model already exists.');
		
		$file = CLI_PATH.'tpl'.DIRECTORY_SEPARATOR.'model.class.tpl';
		if(!is_file($file)) self::halt('model file not existent.');
        $data = file_get_contents($file);
        $content = str_replace('{MODEL}', $parameter[2], $data);
        $len = @file_put_contents($model_file, $content);
		$len ? self::write('Model created successfully.', 'green') : self::write('Model created failed.', 'red');
	}


	/**
     * 创建队列
     */	
	public function job() {

		$parameter = self::$parameter;
		$job = $parameter[1];
		$job_file = YZMPHP_PATH.'jobs'.DIRECTORY_SEPARATOR.$job.EXT;
		if(is_file($job_file)) self::halt($job.' already existed.');
		is_dir(YZMPHP_PATH.'jobs') || @mkdir(YZMPHP_PATH.'jobs', 0755);
		
		$file = CLI_PATH.'tpl'.DIRECTORY_SEPARATOR.'job.class.tpl';
		if(!is_file($file)) self::halt('job file not existent.');
		self::empty_index(YZMPHP_PATH.'jobs'.DIRECTORY_SEPARATOR);
        $data = file_get_contents($file);
        $content = str_replace(array('{JOB}'), array($parameter[1]), $data);
        $len = @file_put_contents($job_file, $content);
		$len ? self::write('Job created successfully.', 'green') : self::write('Job created failed.', 'red');
		
	}
	
	
    /**
     * 创建空白index.html
     */		
	private static function empty_index($path) {
		return @file_put_contents($path.'index.html', '');
	}
	
}