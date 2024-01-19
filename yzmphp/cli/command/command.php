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
		'make job test_job' => '创建test_job队列文件',
	),
	'queue' => array(
		'queue work' => '启动队列工作',
		'queue lists' => '查看队列列表，-queue=test，查看指定队列(可选)',
		'queue restart' => '重启队列工作',
		'queue failed' => '查看所有失败的任务',
		'queue retry' => '重试失败任务，需指定一个任务ID（如 queue retry -id=xxxxx）',
		'queue delete' => '删除单个失败任务，需指定一个任务ID（如 queue delete -id=xxxxx）',
		'queue flush' => '删除所有失败任务',
		'queue table' => '创建队列表',
	),
	'test' => array(
		'test' => '命令测试专用',
	),
);