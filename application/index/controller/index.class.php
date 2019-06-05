<?php
class index{
	
	//首页
	public function init() {
		$title = '欢迎使用YZMPHP框架';
		include template('index', 'index');
	}
}