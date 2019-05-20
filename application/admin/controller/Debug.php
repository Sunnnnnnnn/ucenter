<?php
namespace app\admin\controller;

class Debug extends \think\Controller
{
	public function index()
	{
		

		$this->assign('null',null);
		return $this->fetch();
	}
}
