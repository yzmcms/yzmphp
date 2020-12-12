<?php 
/**
 *  说明：所有cli命令集合
 *  每个值即是一个cli命令，新建一个cli命令需要在本目录下新建一个对应的命令操作类
 *
 *  @return    array
 */

return array(
	'help' => array(
		'help' => '命令帮助',
	),
	'make' => array(
		'make module test' => '创建test模块',
		'make module test test' => '创建test模块的同时并在该模块下创建test控制器',
		'make controller test mytest' => '在test模块下创建mytest的控制器',
		'make model test mytest' => '在test模块下创建mytest的模型',
	),
	'test' => array(
		'test' => '命令测试专用',
	),
);