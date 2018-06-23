<?php
class index{
	private $db;
	
	function __construct() {  
		$this->db = M('article');  //实例化一个model对象
	}	
	
	//首页
	public function init() {
		//debug(); //用于临时屏蔽debug
		//$article = D('article');  //实例化一个数据表对象
		
		$title = '欢迎使用YZMPHP框架';
		$author = '袁志蒙';
		include template('index', 'index');
	}
	
	
	public function demo(){
		
		//假设这是从数据库中取出的二维数组	
		$users = array(
			array('id' =>'1','name' =>'张三','sex' =>'男','age' =>'21','email' =>'214243830@qq.com'),
			array('id' =>'2','name' =>'李四','sex' =>'女','age' =>'22','email' =>'789546@qq.com'),
			array('id' =>'3','name' =>'王五','sex' =>'女','age' =>'18','email' =>'78454514@qq.com'),
			array('id' =>'4','name' =>'赵六','sex' =>'男','age' =>'28','email' =>'7854454@qq.com'),
			array('id' =>'5','name' =>'孙七','sex' =>'女','age' =>'22','email' =>'123456@qq.com'),
		);

		$rownum = 5;
		
		$title = '欢迎使用YZMPHP框架';
		$author = '袁志蒙';
		include template('index', 'demo');
	}
	
	public function test(){ 
		//获取数据列表,这里需配置好数据库
		$res = $this->db->getinfo(5);
		P($res);
		
	
/*   
        //数据分页实例
		$article = D('article');
        $total = $article->total();  
		yzm_base::load_sys_class('page','',0);
		$page = new page($total, 10);
		$res = $article->order('id DESC')->limit($page->limit())->select();
		$pages = $page->getfull();
		
		echo $pages; 
*/
	}
}