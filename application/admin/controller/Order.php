<?php
namespace app\admin\controller;

use think\Db;

class Order extends Base
{
	public $pageSize = 25;

	public function index($paystatus = '')
	{
		$search = input('post.search');
		$where = ['o.closed' => 0];

		if ($paystatus === '') {
			$where['paystatus'] =['neq',1];
		}else{
			if ($paystatus != (string)(int)$paystatus) {
				$this->error('参数异常！','order/index');
			}
			$where['paystatus'] = $paystatus; 
		}

		$cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('p_cate_ids',$cate_ids);
        $cates = get_last_cate(15);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('s_cate_ids',$cate_ids);
        $cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('e_cate_ids',$cate_ids);
        $user_level = $this->getUserLevel(session('uid'));
        $this->assign('user_level',$user_level);


		if ($search!= false) {
			$list = db("u_order o")
			->field('o.*,f.mobile')
			->join('u_order_field f','f.order_id=o.order_id')
			->where($where)
			->where(['o.order_id|mobile'=>$search])
			->order("o.order_id DESC")
			->paginate($this->pageSize,false,array(
				));
		}else{
			$list = db("u_order o ")
			->field('o.*,f.mobile')
			->join('u_order_field f','f.order_id=o.order_id')
			->where($where)
			->order("o.order_id DESC")
			->paginate($this->pageSize,false,array(
				));
		}
		
		
		$this->assign('search',$search);
		$this->assign('paystatus',$paystatus);
    		//  总数据
		$this->assign('total',$list->total());
    		//  总页数
		$total= ceil($list->total() / $this->pageSize);
		$this->assign('totalPage', $total);
    		//
    	

		$this->assign('list',$list);

		return $this->fetch();
	}

	public function edit($order_id = false){
		if ($order_id == false || $order_id != (string)(int)$order_id) {
			$this->error('参数异常！','order/index');
		}
		$data = input('post.');
		if ($data) {
			$data['dateline'] = time();
			$data['paystatus'] = 0;
			$thumb = uploadFile('cover','order'); 
			if ($thumb) {
				$data['cover'] = $thumb['img'];
			}
			$res = db('u_order')->where(array('order_id' =>$order_id))->update($data);

			if ($res != false) {
				$this->success('修改成功！');
			}else{
				$this->error('服务器异常！','order/index');
			}
		}else{

			$order = db('u_order')->where(array('order_id' =>$order_id))->find();
			$this->assign('order',$order);
			return $this->fetch();
		}

	}

	public function setpaystatus($order_id,$value = 3){
		if ($order_id == false ) {
			$this->error('参数异常！','order/index');
		}

		$cates = get_last_cate(1);
			$cate_ids = array_key_value($cates,'cate_id');
			$cates = get_last_cate(1);
	        $p_cate_ids = array_key_value($cates,'cate_id');
	        $cates = get_last_cate(15);
	        $s_cate_ids = array_key_value($cates,'cate_id');
	        $user_level = $this->getUserLevel(session('uid'));
	        $cates = get_last_cate(2);
	        $e_cate_ids = array_key_value($cates,'cate_id');


		Db::startTrans();
		try {
			if (!$order = db('u_order')->where(['order_id'=>$order_id])->find()) {
				Db::rollback();
				alert_output('订单不存在!');
			}
			if (db('u_order')->where(array('order_id' =>$order_id))->update(array('paystatus'=>$value)) !== false) {
				if ($value == 3 || $value == 1) {
					//更新商品库存
					$cates = get_last_cate(1);
        			$cate_ids = array_key_value($cates,'cate_id');

					$order_countent = db('u_order_content')->where(['order_id'=>$order_id])->select();
					if ($order_countent) {
						foreach ($order_countent as $key => $product) {
							$data = db('u_product')->where(['product_id'=>$product['product_id'],'closed' =>0])->find();
							if ($data == false) {
								Db::rollback();
								alert_output('商品不存在或已下架');
							}

							$point = 0;
							if (in_array($data['cate_id'], $p_cate_ids)) {
								if ($data['stock']  >= $product['count'] ) {
									db('u_product')->where(['product_id'=>$product['product_id']])->update(['stock'=>($data['stock']-$product['count'])]);
									$point = $point + $data['price'];
								}else{
									Db::rollback();
									alert_output('商品库存不足!');
								}
							}elseif (in_array($data['cate_id'], $s_cate_ids)) {
								if ($user_level <3) {
									$point = $point  +$data['price'] ;
								}
							}elseif (in_array($data['cate_id'], $e_cate_ids)) {
								if ($user_level <2) {
									$point = $point  +$data['price'] ;
								}
							}
							
						}
					}
					//更新会员积分
					

					if($user_field = db('u_member_field')->where(['uid'=>$order['uid']])->find()){
						db('u_member_field')->where(['uid'=>$order['uid']])->setInc('point',$point);
					}else{
						db('u_member_field')->insert(['uid'=>$order['uid'],'point'=>$point]);
					}

				}
				Db::commit();
				alert_output('操作成功!','');
			}else{
				Db::rollback();
				alert_output('服务器异常!','');
			}



		} catch (\Exception $e) {
		    Db::rollback();
			alert_output('服务器异常!','');

		}
		
	}

	public function setclosed($order_id,$value = 1){
		if ($order_id == false || $order_id != (string)(int)$order_id) {
			$this->error('参数异常！','order/index');
		}

		if (db('u_order')->where(array('order_id' =>$order_id))->update(array('closed'=>$value)) !== false) {
			$this->success('操作成功！');
		}else{
			$this->error('服务器异常！','order/index');
		}
	}

}
