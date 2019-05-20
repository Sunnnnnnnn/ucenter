<?php
namespace app\member\controller;


class Index extends Base
{
	
	public function index(){
		$uid = session('uid');
		if($uid == false || $uid != (string)(int)$uid){
			alert_output('参数错误!','index.php');
		}
		
		$article = db('u_article')->where(['cate_id' =>4,'closed'=>0 ])->limit(10)->select();
		$news = db('u_article')->where(['cate_id' =>5,'closed'=>0 ])->limit(10)->select();
		$data['news'] = $news;
		$data['article'] = $article;
		$this->assign('data',$data);
		return $this->fetch();
	}
}
