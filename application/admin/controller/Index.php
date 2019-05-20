<?php
namespace app\admin\controller;
use app\admin\model\Admin;

class Index extends Base
{
	public function index()
	{
		$this -> redirect('user/index');

		$user = Admin::all();
		if($user) {
			$data = collection($user)->toArray();
		}
		$a =new Admin();
		$line = $a -> getQuery();
		if($line) {
			$line = collection($line)->toArray();
		}

		return $this->fetch();
	}
}
