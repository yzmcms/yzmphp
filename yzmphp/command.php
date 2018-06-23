<?php 
/**
 * cli模式入口文件
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-04-16 
 */

define('APP_DEBUG', true);
define('YZMPHP_PATH', dirname(dirname(__FILE__).DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
define('URL_MODEL', '3');  
require(YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'yzmphp.php'); 
yzm_base::load_sys_class('debug');
define('CLI_PATH', YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR);
require(CLI_PATH.'cli'.EXT);
if(PHP_SAPI != 'cli') cli::halt('Not cli mode.');
cli::start($_SERVER['argv']);
