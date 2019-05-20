<?php 
namespace app\index\controller;

class Product extends Base
{
	
	public function index($cate_id = ''){

		$cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
		if ($cate_id == (string)(int)$cate_id) {
			$where['cate_id'] = $cate_id;
		}
		$q = input('get.q');
		if ($q) {
			$where['name'] = ['like','%'.$q.'%'];
		}
		$this->assign('cate_id',$cate_id);

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
		}else{
			alert_output('商品不存在或已下架!',get_index('product'));
		}
		$this->assign('product',$product);
		//热门推荐
		$hots = db('u_product')->where(array('closed' => 0))->limit(4)->select();
		$this->assign('hots',$hots);
		return $this->fetch();
	}

	public function addcart()
	{
		$uid = session('uid');
		if ($uid == false) {
			json_output(101,'参数错误!');
		}
		$product_id = input('post.product_id');
		$count = input('post.count');
		if ($cart = db('u_cart')->where(['uid'=>$uid,'product_id'=>$product_id])->find()) {
			$count = $cart['count'] + $count;
			if (db('u_cart')->where(['uid'=>$uid,'product_id'=>$product_id])->update(['count' => $count,'dateline'=>time()])) {
				json_output(200,'更新成功!');
			}else{
				json_output(501,'服务器异常,请稍后再试!');
			}
		}else{
			$data['product_id'] = $product_id;
			$data['uid'] = $uid;
			$data['count'] = $count;
			$data['dateline'] = time();
			if (db('u_cart')->insert($data)) {
				json_output(200,'更新成功!');
			}else{
				json_output(501,'服务器异常,请稍后再试!');
			}
		}
	}


}