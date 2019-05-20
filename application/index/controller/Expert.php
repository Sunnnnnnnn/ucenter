<?php 
namespace app\index\controller;

class Expert extends Base
{
	
	public function index($cate_id = '',$province_id = ''){
		$pageSize = 9;
		$page = input('?get.page') ? input('get.page') : 1;
		$this->assign('page',$page);
		$cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
		if ($cate_id == (string)(int)$cate_id) {
			$where['cate_id'] = $cate_id;
		}
		$q = input('get.q');
		if ($q) {
			$where['name'] = ['like','%'.$q.'%'];
		}
		if ($province_id == (string)(int)$province_id) {
			$where['province_id'] = $province_id;
		}
		$pageStart = ($page -1)*$pageSize;
		$catesCount = db('u_product') ->where($where)->count();
		$pageCount = ceil ($catesCount / $pageSize );
		$this->assign('pageCount',$pageCount);
		
		$experts = db('u_product')->where($where)->limit($pageStart,$pageSize)->select();
		$this->assign('experts',$experts);
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>2))->select();
		$this->assign('cates',$cates);
		$provinces = db('data_province')->select();
		$this->assign('provinces',$provinces);
		return $this->fetch();
	}

	public function detail($product_id =''){
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>2))->select();
		$this->assign('cates',$cates);
		$provinces = db('data_province')->select();
		$this->assign('provinces',$provinces);
		$cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids],'product_id'=>$product_id];
		$expert = db('u_product')->where($where)->find();
		if ($expert) {
			foreach ($expert as $key => $value) {
				if ($key == 'spec') {
					$expert[$key] = explode(';', $value);
				}
			}
		}
		$this->assign('expert',$expert);
		//热门推荐
		$hots = db('u_product')->where(array('closed' => 0))->limit(4)->select();
		$this->assign('hots',$hots);
		return $this->fetch();
	}

	public function map(){
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>2))->select();
		$this->assign('cates',$cates);
		$provinces = db('data_province')->select();
		$this->assign('provinces',$provinces);
		return $this->fetch();
	}

	public function getListByProvince(){
		$cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
		$list = db('u_product u') ->field('u.*,p.province_name')->join('data_province p','p.province_id=u.province_id')->where($where)->select();
		$temp = [];
		foreach ($list as $key => $value) {
			$value['desc'] = mb_substr(strip_tags($value['desc']), 0,15,'utf-8');
			if (array_key_exists($value['province_id'], $temp)) {
				if(count($temp[$value['province_id']])<4){
					$temp[$value['province_id']][] =$value;
				}
			}else{
					$temp[$value['province_id']][] =$value;
			}
			
		}
		$temp2 = [];
		foreach ($temp as $key => $value) {
			foreach ($value as  $v) {
				$temp2[] = $v;
			}
		}
		if ($temp2!== false) {
			json_output(200,'',$temp2);
		}
		json_output(501,'',$temp2);
	}
}