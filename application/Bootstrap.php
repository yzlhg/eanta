<?php
/**
 * @name Bootstrap引导程序 只是引导的一部分是可以选择
 * @author seven
 * @desc 所有Bootstrap类中，以_init开头的方法，都会被Yaf调用
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract {

	//获取配置文件参数
	public function _initConfig(Yaf\Dispatcher $dispatcher) {
		Yaf\Registry::set("config",Yaf\Application::app()->getConfig());
	}

	//开启session
	public function _initSession(Yaf\Dispatcher $dispatcher) {
		Yaf\Session::getInstance()->start();
	}


}