<?php
namespace app\member\controller;


class Message extends Base
{
	
	public function index(){
		$uid = session('uid');
		if($uid == false || $uid != (string)(int)$uid){
			alert_output('参数错误!','index.php');
		}
		$uid = 1073;
		$user = db('member')->where(['uid' => $uid,'closed' => 0])->find();
		$this->assign('user',$user);
		$news = db('u_article')->where(['cate_id' =>4,'closed'=>0 ])->limit(10)->select();
		$article = db('u_article')->where(['cate_id' =>5,'closed'=>0 ])->limit(10)->select();
		$data['news'] = $news;
		$data['article'] = $article;
		$this->assign('data',$data);
		return $this->fetch();
	}
}
