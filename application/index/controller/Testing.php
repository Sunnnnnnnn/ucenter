<?php 
namespace app\index\controller;

class Testing extends Base
{
	
	public function index($cate_id = ''){
		$where = array('closed' => 0);
		if ($cate_id == (string)(int)$cate_id) {
			$where['cate_id'] = $cate_id;
		}
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>1))->select();
		$this->assign('cates',$cates);

		$pageSize = 9;
		$page = input('?get.page') ? input('get.page') : 1;
		$this->assign('page',$page);
		$pageStart = ($page -1)*$pageSize;
		$catesCount = db('u_product') ->where($where)->count();
		$pageCount = ceil ($catesCount / $pageSize );
		$this->assign('pageCount',$pageCount);
		$products = db('u_product')->where($where)->limit($pageStart,$pageSize)->select();
		$this->assign('products',$products);
		
		return $this->fetch();
	}

	public function detail($product_id =''){
		
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>1))->select();
		$this->assign('cates',$cates);
		$product = db('u_product')->where(array('closed'=>0,'product_id'=>$product_id))->find();
		if ($product) {
			foreach ($product as $key => $value) {
				if ($key == 'spec') {
					$product[$key] = explode(';', $value);
				}
			}
		}
		$this->assign('product',$product);
		//热门推荐
		$hots = db('u_product')->where(array('closed' => 0))->limit(4)->select();
		$this->assign('hots',$hots);
		return $this->fetch();
	}


}