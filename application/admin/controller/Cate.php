<?php
namespace app\admin\controller;

class Cate extends Base
{

	public $pageSize = 100;

	public function index()
	{
		$search = input('post.search');
		if ($search!= false) {
			$list = db("u_cate")
			->where('name|cate_id','like','%'.$search.'%')
			->where(array(
				'closed' => 0
				))
			->order("cate_id ASC")
			->select();
		}else{
			$list = db("u_cate")
			->where(array(
				'closed' => 0,
				))
			->order("cate_id ASC")
			->select();
		}

		$this->assign('search',$search);

		if ($list) {
			$arr = array();
			foreach ($list as $key => $value) {
				if ($value['parent_id'] == 0) {
					$value['subs'] = array();
					$arr[$value['cate_id']] = $value;
				}else{
					if (array_key_exists($value['parent_id'], $arr)) {
						$arr[$value['parent_id']]['subs'][$value['cate_id']] = $value;
					}else{
						foreach ($arr as $k => $v) {
							if (array_key_exists($value['parent_id'], $v['subs'])) {
								$arr[$k]['subs'][$value['parent_id']]['subs'][$value['cate_id']] = $value;
							}
						}
					}
				}

			}
		}
		$this->assign('list',$arr);
		return $this->fetch();

	}

	public function edit($cate_id = false){
		if ($cate_id == false || $cate_id != (string)(int)$cate_id) {
			$this->error('参数异常！','cate/index');
		}
		$data = input('post.');
		if ($data) {
			if (array_key_exists('son_id', $data) && $data['son_id'] != false) {
				$data['parent_id'] = $data['son_id'];
			}
			unset($data['son_id']);
			if(db('u_cate') -> where('cate_id','neq',$cate_id)-> where(array('name' =>$data['name']))->find()){
				$this->error('分类名重复!');
			}
			$data['dateline'] = time();
			$data['closed'] = 0;
			$res = db('u_cate')->where(array('cate_id' =>$cate_id))->update($data);
			if ($res != false) {
				$this->success('修改成功！','cate/index');
			}else{
				$this->error('服务器异常！','cate/index');
			}
		}else{
			$cate = db('u_cate')->where(array('cate_id' =>$cate_id))->find();
			$this->assign('cat',$cate);
			$cates = db('u_cate')->where(array('closed' =>0,'parent_id' =>0))->select();
			$this->assign('cates',$cates);
			return $this->fetch();
		}

	}

	public function add(){
		$data = input('post.');
		if ($data) {

			if(db('u_cate') -> where(array('name' =>$data['name']))->find()){
				$this->error('分类名重复!');
			}
			if (array_key_exists('son_id', $data) && $data['son_id'] != false) {
				$data['parent_id'] = $data['son_id'];
			}
			unset($data['son_id']);

			$data['dateline'] = time();
			$data['closed'] = 0;
			if (db('u_cate')->insert($data)) {
				$this->success('新增分类信息成功！','cate/index');
			}else{
				$this->error('服务器异常！','cate/index');
			}
		}else{
			$cates = db('u_cate')->where(array('closed' =>0,'parent_id' =>0))->select();
			$this->assign('cates',$cates);
			return $this->fetch();
		}
	}

	public function delete($cate_id){
		if ($cate_id == false || $cate_id != (string)(int)$cate_id) {
			$this->error('参数异常！','cate/index');
		}

		if (db('u_cate')->where(array('cate_id' =>$cate_id))->update(array('closed'=>1))) {
			$this->success('删除资料成功！');
		}else{
			$this->error('服务器异常！','cate/index');
		}
	}

	public function get2cates(){
		$cate_id = input('post.cate_id');
		if ($cate_id == false || (string)(int)$cate_id != $cate_id) {
			show_output(101,'参数异常！','cate/index');

		}
		$cates = db('u_cate')->where(array('closed'=>0,'parent_id'=>$cate_id))->select();
		
		show_output(200,'message',$cates);
	}

}



