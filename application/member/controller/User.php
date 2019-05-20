<?php

namespace app\member\controller;

use \think\DB;



class User extends Base

{

	public function index($value='')
	{
		
		return view('index');

	}

	

}

