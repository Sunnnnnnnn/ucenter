<?php 
namespace app\index\controller;

class Service extends Base
{
	
	public function detection($cate_id = ''){

		$cates = get_last_cate(17);
	    $cate_ids = array_key_value($cates,'cate_id');
	    $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
		if ($cate_id == (string)(int)$cate_id && $cate_id != false) {
			$where['cate_id'] = $cate_id;
		}
		$q = input('get.q');
		if ($q) {
			$where['name'] = ['like','%'.$q.'%'];
		}
		$services = db('u_product')->where($where)->select();
		$this->assign('services',$services);
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>17))->select();
		$this->assign('cates',$cates);
		return $this->fetch();
	}

	public function authentication(){
		$cates = get_last_cate(16);
	    $cate_ids = array_key_value($cates,'cate_id');
	    $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
		$where['cate_id'] = 16;
		$service = db('u_product')->where($where)->find();
		$this->assign('service',$service);
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>17))->select();
		$this->assign('cates',$cates);
		return $this->fetch();
	}




}