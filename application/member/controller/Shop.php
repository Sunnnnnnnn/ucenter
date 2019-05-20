<?php
namespace app\member\controller;
use \think\Db;

class Shop extends Base
{
	
	public function cart(){
		$cart = db('u_cart c')->field('c.*,p.name as product_name,p.price,p.thumb_cover,p.unit')->join('u_product p','p.product_id=c.product_id')->where(['uid' => session('uid'),'p.closed' =>0])->order('dateline desc')->select();
		$this->assign('cart',$cart);
		//会员级别,享受优惠
		return $this->fetch();
	}

	public function cartpay()
	{
		$order_no = input('order_no');
		if ($order_no == false) {
			alert_output('参数错误!');
		}
		$this->assign('order_no',$order_no);

		$cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('p_cate_ids',$cate_ids);
        $cates = get_last_cate(15);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('s_cate_ids',$cate_ids);
        $user_level = $this->getUserLevel(session('uid'));
        $this->assign('user_level',$user_level);
         $cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('e_cate_ids',$cate_ids);

		if ($order = db('u_order o')->where(['o.uid' => session('uid') , 'o.order_id' =>$order_no])->find()) {

			$orders = db('u_order_content')->where(['order_id' => $order_no])->select();
			foreach ($orders as $key => $value) {
				$product = db('u_product')->where(['product_id'=>$value['product_id']])->find();
				$orders[$key]['cover'] = $product['cover'];
				$orders[$key]['cate_id'] = $product['cate_id'];
			}
			$order['orders'] = $orders;
			$this->assign('order',$order);
			//默认地址
			$address = db('u_address')->where(['uid'=>session('uid'),'closed' => 2])->find();
			if ($address == false) {
				$address = [
					'mobile' => '',
					'mail' =>'',
					'realname' => '',
					'telephone' => '',
					'address' => '',
					'addressnum' => ''
				];
			}
			$this->assign('address',$address);
			return $this->fetch();
		}else{
			alert_output('订单不存在');
		}
	}

	public function confirm()
	{
		$data = input('post.');
		var_dump($data);
	}

	public function order()
	{

		$where = ['uid' => session('uid'),'closed' =>0];
		$data = input('get.');
		$page = input('?get.page') ? input('get.page') :1;
		unset($data['page']);

		if ($data) {
			if(array_key_exists('keyboard', $data) && $data['keyboard']){
				$where['order_id'] = $data['keyboard'];
			}
			if (array_key_exists('starttime', $data) && $data['starttime']) {
				$where['dateline'] = ['egt',strtotime($data['starttime'])];
			}
			if (array_key_exists('endtime', $data) && $data['endtime']) {
				$where['dateline'] = ['lt',strtotime($data['endtime'])];
			}
		}

		$list = db('u_order')->where($where)->order('dateline desc')->paginate(25, false, ['query' => request()->param()]);
		$this->assign('list',$list);
		return $this->fetch();
	}

	public function orderdetail()
	{

		$order_id = input('order_no');
		$uid = session('uid');
		if ($order_id == false ) {
			alert_output('参数错误!',get_member());
		}
		if (input('uid')) {
			$uid = input('uid');
		}
		
		$cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('p_cate_ids',$cate_ids);
        $cates = get_last_cate(15);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('s_cate_ids',$cate_ids);
        $user_level = $this->getUserLevel($uid);
        $this->assign('user_level',$user_level);
         $cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $this->assign('e_cate_ids',$cate_ids);

		$order = db('u_order o')->where(['o.order_id' => $order_id])->find();
		if ($order) {
			$products = db('u_order_content o')->field('o.*,p.cover,p.cate_id')->join('u_product p','o.product_id=p.product_id')->where(['order_id'=>$order_id])->select();
			$order['products'] = $products;
			$field = db('u_order_field')->where(['order_id'=>$order_id])->find();
			$order['field'] = $field;
		}else{
			alert_output('订单不存在!',get_member());
		}
		$this->assign('order',$order);
		return $this->fetch();
	}

	public function addorder()
	{

		$cart_ids = input('post.')['cart_id'];
		if ($cart_ids == false || !is_array($cart_ids)) {
			json_output(101,'');
		}
		//生成订单号
		$order_no = substr(date('YmdHis') . session('uid'), 2) ;
		$start = 1;
		$end = 9;
		for ($i=1; $i < 20 - strlen($order_no); $i++) { 
			$start .= 0;
			$end .= 9;
		}

		$order_no =$order_no . mt_rand($start,$end);

		
		Db::startTrans();
		try {
			$data = db('u_cart')->where(['uid' => session('uid'),'cart_id' => ['in',$cart_ids]])->select();

			$arr = array();
			$money = 0;
			foreach ($data as $key => $value) {
				$product = db('u_product') ->where(['product_id' => $value['product_id']])->find();
				$arr[$key]['order_id'] = $order_no;
				$arr[$key]['product_id'] = $product['product_id'];
				$arr[$key]['product_name'] = $product['name'];
				$arr[$key]['price'] = $product['price'];
				$arr[$key]['count'] = $value['count'];
				$money += ($value['count'] * $product['price']);
			}

		    db('u_order')->insert([
		    	'order_id' => $order_no,
				'uid' => session('uid'),
				'paystatus' => 0,
				'money' => $money,
				'dateline' => time(),
				'closed' => 0
				]);

			db('u_order_content') ->insertAll($arr);
			
			db('u_cart')->where(['cart_id' => ['in',$cart_ids]])->delete();

		    Db::commit();
			json_output(200,'',$order_no);
		} catch (\Exception $e) {
		    Db::rollback();

			json_output(501,'服务器异常,请稍后再试!');
		}
		
	}

	public function addOrderByProductId(){
		$product_id = input('post.product_id');
		if ($product_id != (string)(int)$product_id) {
			json_output(101,'');
		}
		//生成订单号
		$order_no = substr(date('YmdHis') . session('uid'), 2) ;
		$start = 1;
		$end = 9;
		for ($i=1; $i < 20 - strlen($order_no); $i++) { 
			$start .= 0;
			$end .= 9;
		}

		$order_no =$order_no . mt_rand($start,$end);
		Db::startTrans();
		try {

			$arr = array();
			$money = 0;
			$product = db('u_product') ->where(['product_id' => $product_id])->find();
			$arr['order_id'] = $order_no;
			$arr['product_id'] = $product['product_id'];
			$arr['product_name'] = $product['name'];
			$arr['price'] = $product['price'];
			$arr['count'] = 1;
		    db('u_order')->insert([
		    	'order_id' => $order_no,
				'uid' => session('uid'),
				'paystatus' => 0,
				'money' => $product['price'],
				'dateline' => time(),
				'closed' => 0,
				'type' => input('post.type')
				]);

			db('u_order_content') ->insert($arr);
			

		    Db::commit();
			json_output(200,'',$order_no);
		} catch (\Exception $e) {
		    Db::rollback();

			json_output(501,'服务器异常,请稍后再试!',db()->getLastSql());
		}
	}



	public function address()
	{

		$address = db('u_address')->where(['uid' => session('uid'),'closed' => ['neq',1]])->select();
		$this->assign('address',$address);

		return $this->fetch();
	}



	public function editaddress($address_id='',$closed='')
	{
		if ($address_id == false || (string)(int)$address_id != $address_id) {
			alert_output('参数错误!');
		}
		if($closed != false && in_array($closed, [0,1,2])){
			db('u_address') ->where(['uid' => session('uid'),'closed' => 2])->update(['closed'=>0]);
			if(db('u_address') ->where(['uid' => session('uid'),'address_id' => $address_id])->update(['closed'=>$closed])){
				alert_output('修改成功!');
			}else{
				alert_output('服务器异常!','');
			}
		}
		if ($data = input('post.')) {
			if (db('u_address') ->where(['uid' => session('uid'),'address_id' => $address_id])->update($data)) {
				alert_output('修改成功!');

			}else{
				alert_output('服务器异常!','');
			}
		}
		$address = db('u_address') ->where(['uid' => session('uid'),'address_id' => $address_id])->find();
		$this->assign('address',$address);
		return $this->fetch();
	}

	public function addaddress()
	{

		if ($data = input('post.')) {
			$data['uid'] = session('uid');
			$data['closed'] = 0;
			if(db('u_address') ->insert($data)){
				alert_output('添加成功!',get_member('shop/address'));
			}else{
				alert_output('服务器异常!','');
			}
		}
		return $this->fetch();
	}

	public function pay()
	{
		$data = input('post.');
		if ($data == false) {
			alert_output('参数错误!','');
		}
		$order = db('u_order')->where(['uid'=>session('uid'),'order_id' =>$data['order_no']])->find();
		if ($order == false) {
			alert_output('订单不存在!','');
		}

		


		$order_id = $data['order_no'];

		if ($data['paytype'] == 1) {
			unset($data['paytype']);
			if (db('u_order_field')->where(['order_id' =>$data['order_no']])->find()) {
				unset($data['order_no']);
				db('u_order_field')->where(['order_id' =>$order_id])->update($data);
			}else{
				$data['order_id'] = $data['order_no'];
				unset($data['order_no']);
				db('u_order_field')->insert($data);
			}
			DB::startTrans();
			try {
				
				db('u_order')->where(['order_id'=>$order_id,'uid'=>session('uid')])->update(['paystatus'=>2]);

			    Db::commit();
				alert_output('提交成功,请与工作人员联系!',get_member('shop/order'));
			} catch (\Exception $e) {
			    Db::rollback();
				alert_output('服务器异常!','');

			}
		}else{
			$user_discount = $this->getUserDiscount(session('uid'));
			$user_point = $this->getUserPoint(session('uid'));
			
			unset($data['paytype']);
			if (db('u_order_field')->where(['order_id' =>$order_id])->find()) {
				unset($data['order_no']);
				db('u_order_field')->where(['order_id' =>$order_id])->update($data);
			}else{
				$data['order_id'] = $data['order_no'];
				unset($data['order_no']);
				db('u_order_field')->insert($data);
			}

			DB::startTrans();
			try {
				$user_level = get_user_level(session('uid'));
				
				$money = 0;
				db('u_order')->where(['order_id'=>$order_id])->update(['paystatus' => 4]);
				//更新商品库存
				$cates = get_last_cate(1);
    			$cate_ids = array_key_value($cates,'cate_id');
    			$cates = get_last_cate(1);
		        $p_cate_ids = array_key_value($cates,'cate_id');
		        $cates = get_last_cate(15);
		        $s_cate_ids = array_key_value($cates,'cate_id');
		        $user_level = $this->getUserLevel(session('uid'));
		        $cates = get_last_cate(2);
		        $e_cate_ids = array_key_value($cates,'cate_id');
    			
				$order_countent = db('u_order_content')->where(['order_id'=>$order_id])->select();
				if ($order_countent) {
					foreach ($order_countent as $key => $product) {
						$data = db('u_product')->where(['product_id'=>$product['product_id'],'closed' =>0])->find();
						if ($data == false) {
							Db::rollback();
							alert_output('商品不存在或已下架!');
						}
						if (in_array($data['cate_id'], $p_cate_ids)) {
							if ($data['stock']  >= $product['count'] ) {
								db('u_product')->where(['product_id'=>$product['product_id']])->update(['stock'=>($data['stock']-$product['count'])]);
								$money = $money + $data['price'] * 10;
							}else{
								Db::rollback();
								alert_output('商品库存不足!');
							}
						}elseif (in_array($data['cate_id'], $s_cate_ids)) {
							if ($user_level <3) {
								$money = $money  +$data['price'] * 10 ;
							}
						}elseif (in_array($data['cate_id'], $e_cate_ids)) {
							if ($user_level <2) {
								$money = $money  +$data['price'] * 10 ;
							}
						}
						
					}
				}
				//
				$money = round($money);
				if ($money > $user_point) {
					alert_output('积分不足!','');
				}
				db('u_member_field')->where(['uid'=>session('uid')])->setDec('point',$money);
			    Db::commit();
				alert_output('积分购买成功!',get_member('shop/order'));
			} catch (\Exception $e) {
			    Db::rollback();
				alert_output('服务器异常!','');

			}
		}
	}
}
