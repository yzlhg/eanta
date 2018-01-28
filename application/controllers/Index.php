<?php
class IndexController extends Controller {

	//首页
	public function indexAction() {
		
		//获取主导航条
		$main_nav = $this->db->sql("SELECT FID,FName FROM t_BOSNav")->fetchAll();

		//获取轮播图
		$carousel = common::get_dirs('upload/carousel');

		//获取文档链接
		$docLink = $this->db->sql("SELECT t_BOSIndexLink.FName,t_BOSIndexLink.FNOTE,t_BOSIndexLinkEntry.FDocID FROM t_BOSIndexLink INNER JOIN t_BOSIndexLinkEntry ON t_BOSIndexLink.FID=t_BOSIndexLinkEntry.FID")->fetchAll();
		
		//获取商品信息
		$products = $this->db->sql("SELECT t_ICItem.FItemID,t_ICItem.FNumber,t_ICItem.FName FROM t_ICItem INNER JOIN t_BOSIndexGoodsEntry ON t_BOSIndexGoodsEntry.FICItemID=t_ICItem.FItemID ORDER BY t_BOSIndexGoodsEntry.FIndex")->fetchAll();

		//获取文章列表信息
		$article = $this->db->sql("SELECT t_BOSDoc.FID,t_BOSDoc.FNumber,t_BOSDoc.FName,t_BOSDoc.FNOTE FROM t_BOSDoc INNER JOIN t_BOSIndexDocEntry ON t_BOSIndexDocEntry.FDocID=t_BOSDoc.FID ORDER BY t_BOSIndexDocEntry.FIndex
		")->fetchAll();

		//赋值
		$this->getView()->assign('head',$this->head)->assign('carousel',$carousel)->assign('main_nav',$main_nav)->assign('docLink',$docLink)->assign('article',$article)->assign('products',$products);
	}

	//导航条进入页面
	public function navAction(){
		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		//侧边栏导航
		$side_nav = $this->db->sql("SELECT FID,FName FROM t_BOSSideNav WHERE FDetail=1")->fetchAll();
		
		//获取页面信息
		$pageResult = $this->db->sql("SELECT t_BOSNavDoc.FID,t_BOSNavDocEntry.FDocID,t_BOSDoc.FName,t_BOSDoc.FNOTE FROM t_BOSNavDocEntry INNER JOIN t_BOSNavDoc ON t_BOSNavDoc.FID=t_BOSNavDocEntry.FID INNER JOIN t_BOSDoc ON t_BOSNavDocEntry.FDocID=t_BOSDoc.FID WHERE t_BOSNavDoc.FNavID=".$params['id'])->fetchRow();
		
		//赋值
		$this->getView()->assign('head',$this->head)->assign('pageResult',$pageResult)->assign('side_nav',$side_nav);			
	}

	//边栏进入页面
	public function sideAction(){
		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		//侧边栏导航
		$side_nav = $this->db->sql("SELECT FID,FName FROM t_BOSSideNav")->fetchAll();
		
		//获取页面信息
		$pageResult = $this->db->sql("SELECT t_BOSSideNavDoc.FID,t_BOSSideNavDocEntry.FDocID,t_BOSDoc.FName,t_BOSDoc.FNOTE FROM t_BOSSideNavDocEntry INNER JOIN t_BOSSideNavDoc ON t_BOSSideNavDoc.FID=t_BOSSideNavDocEntry.FID INNER JOIN t_BOSDoc ON t_BOSSideNavDocEntry.FDocID=t_BOSDoc.FID WHERE t_BOSSideNavDoc.FSideNavID=".$params['id'])->fetchRow();
		
		//赋值
		$this->getView()->assign('head',$this->head)->assign('pageResult',$pageResult)->assign('side_nav',$side_nav);			
	}
	
	//文章详情页
	public function pageAction(){
		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		//侧边栏导航
		$side_nav = $this->db->sql("SELECT FID,FName FROM t_BOSCategory where FDetail=1")->fetchAll();

		//获取页面信息
		$pageResult = $this->db->sql("SELECT t_BOSDoc.FName,t_BOSDoc.FNOTE FROM t_BOSDoc
		WHERE t_BOSDoc.FID=".$params['id'])->fetchRow();

		//赋值
		$this->getView()->assign('head',$this->head)->assign('pageResult',$pageResult)->assign('side_nav',$side_nav);			
	}

	//文章列表页
	public function newsAction(){
		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		//侧边栏导航
		$side_nav = $this->db->sql("SELECT FID,FName FROM t_BOSCategory")->fetchAll();

		//获取页面信息
		$listResult = $this->db->sql("SELECT t_BOSDoc.FID,t_BOSDoc.FNumber,t_BOSDoc.FName
		FROM t_BOSCategoryDocEntry INNER JOIN t_BOSCategoryDoc ON t_BOSCategoryDoc.FID=t_BOSCategoryDocEntry.FID 
		INNER JOIN t_BOSCategory ON t_BOSCategoryDoc.FNavID=t_BOSCategory.FID
		INNER JOIN t_BOSDoc ON t_BOSCategoryDocEntry.FDocID=t_BOSDoc.FID
		WHERE t_BOSCategory.FID=".$params['id'])->fetchAll();

		//赋值
		$this->getView()->assign('head',$this->head)->assign('side_nav',$side_nav)->assign('listResult',$listResult);
	}
	
	//产品列表页
	public function productsAction(){
		//侧边栏导航
		$goods_nav = $this->db->sql("SELECT FItemID,FName FROM t_ComCategory WHERE FDeleted=0")->fetchAll();

		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		if(array_key_exists('list',$params)){
			$list = $params['list'];
		} else{
			$list  = 't_ICItem.FComCategoryID';
		}

		//获取商品列表
		$listResult = $this->db->sql("SELECT t_ICItem.FItemID,t_ICItem.FName,t_ICItem.FNumber FROM t_ICItem WHERE t_ICItem.FDeleted=0 AND t_ICItem.FComCategoryID=".$list)->fetchAll();

		//赋值
		$this->getView()->assign('head',$this->head)->assign('listResult',$listResult)->assign('goods_nav',$goods_nav);
	}

	//产品详情页
	public function productDetailAction(){
		//侧边栏导航
		$goods_nav = $this->db->sql("SELECT FItemID,FName FROM t_ComCategory WHERE FDeleted=0")->fetchAll();

		//获取接收到的导航ID
		$params = $this->getRequest()->getParams();
		//此处需要写函数过滤该字段

		//查询商品信息
		$goods_info = $this->db->sql("SELECT FItemID,FNumber,FName FROM t_ICItem WHERE FItemID=".$params['item'])->fetchRow();

		//获取文件夹下面的文件
		$dirs = common::get_dirs("upload/products/".$goods_info['FNumber']);
		foreach ($dirs as $key => $value) {
			$goods_info['dir'][] = '/upload/products/'.$goods_info['FNumber'].'/'.$value;
		}

		//赋值
		$this->getView()->assign('head',$this->head)->assign('goods_nav',$goods_nav)->assign('goods_info',$goods_info);
	}


}
?>