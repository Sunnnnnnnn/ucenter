<?php
namespace app\member\controller;

class News extends Base
{
	
	public function index(){
		$news = db('u_news')->where(['uid'=>session('uid'),'closed'=>['neq',1]])->order('dateline desc')->select();
		$this->assign('news',$news);
		return $this->fetch();
	}

	public function add(){
		if ($data = input('post.')) {
			$data['uid'] = session('uid');
			$data['ip'] = get_real_ip();
			$data['closed'] = 2;
			$data['dateline'] = time();
			if (db('u_news')->insert($data)) {
				alert_output('添加成功!',get_member('news/index'));
			}else{
				alert_output('服务器异常,请稍后再试!',get_member('news/index'));
			}
		}
		return $this->fetch();
	}

	public function edit($news_id){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数异常！','news/index');
		}
		$data = input('post.');
		if ($data) {
			$data['uid'] = session('uid');
			$data['ip'] = get_real_ip();
			$data['closed'] = 2;
			$data['dateline'] = time();
			$res = db('u_news')->where(array('news_id' =>$news_id))->update($data);
			if ($res != false) {
				$this->success('修改成功！');
			}else{
				$this->error('服务器异常！','news/index');
			}
		}else{
			$news = db('u_news')->where(array('news_id' =>$news_id))->find();
			$this->assign('news',$news);
			return $this->fetch();
		}

		return $this->fetch();
	}

	public function delete(){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数异常！','news/index');
		}
		if (db('u_news')->where(array('news_id' =>$news_id))->update(array('closed'=>1))) {
			$this->success('删除成功！');
		}else{
			$this->error('服务器异常！','news/index');
		}

	}
}
