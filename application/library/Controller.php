<?php
//公用基类
class Controller extends Yaf\Controller_Abstract {
	protected $db; //定义数据库
	protected $head; //网站页头参数

	//数据库初始化函数
	public function init() {
		//打开数据库（单例）
		$this->db = pdoModel::getInstance();
		//网站参数
		$this->head = $this->db->sql("SELECT t_BOSParams.FName,t_BOSParams.FNote,t_BOSParamsEntry.FKeywords,t_BOSParamsEntry.FAuthor FROM t_BOSParams,t_BOSParamsEntry WHERE t_BOSParams.FDetail=1 AND t_BOSParams.FID = t_BOSParamsEntry.FID")->fetchRow();
	}
}