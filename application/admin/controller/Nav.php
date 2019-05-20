<?php
namespace app\admin\controller;
use app\admin\model\Admin;

class Nav extends Base
{
	public function index()
	{

		
		$s = db('u_expert')->select();
		foreach ($s as $key => $value) {
			$s[$key]['spec'] = $value['desc'];
			unset($s[$key]['desc']);
			unset($s[$key]['expert_id']);
		}
		db('u_product')->insertAll($s);

		return $this->fetch();
	}
}
