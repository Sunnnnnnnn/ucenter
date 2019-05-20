<?php
namespace app\member\controller;


class Info extends Base
{
	
	public function editinfo(){
		
		$news = db('u_article')->where(['cate_id' =>4,'closed'=>0 ])->limit(10)->select();
		$article = db('u_article')->where(['cate_id' =>5,'closed'=>0 ])->limit(10)->select();
		$data['news'] = $news;
		$data['article'] = $article;
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function update(){
		$uid = 0;
		if($data = input('post.')){
			if (array_key_exists('uid', $data)) {
				$uid = $data['uid'];
				unset($data['uid']);
			}else{
				alert_output('参数异常!');
			}
		}
		$thumb = uploadFile('u_face','user'); 
		if ($thumb) {
			$data['u_face'] = $thumb['img'];
		}
		if (db('member')->where(['uid'=>$uid])->update($data)) {
			alert_output('修改成功');
		}else{
			alert_output('服务器异常!请稍后再试!');
		}
	}

	public function editsafeinfo(){
		return $this->fetch();
	}

	public function updatesafe(){
		$uid = 0;
		if($data = input('post.')){
			if (array_key_exists('uid', $data)) {
				$uid = $data['uid'];
				unset($data['uid']);
			}else{
				alert_output('参数异常!');
			}
		}
		if ($data['password'] != $data['repassword']) {
			alert_output('两次密码不一致!');
		}

		if (db('member')->where(['uid'=>$uid,'passwd' => md5($data['oldpassword'])])->update(['passwd'=>md5($data['password'])])) {
			alert_output('修改成功!');
		}else{
			alert_output('密码错误!');
		}
	}

	public function my()
	{
		return $this->fetch();
	}

}
