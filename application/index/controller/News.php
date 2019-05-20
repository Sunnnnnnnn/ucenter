<?php 
namespace app\index\controller;

class News extends Base
{
	
	public function index($cate_id = ''){
		$where = array('u.closed' => 0);
		if ($cate_id == (string)(int)$cate_id) {
			$where['cate_id'] = $cate_id;
		}

		$pageSize = 2;
		$page = input('?get.page') ? input('get.page') : 1;
		$this->assign('page',$page);
		$pageStart = ($page -1)*$pageSize;
		$catesCount =  db('u_news u')->join('member m','u.uid=m.uid')->where($where)->count();
		$pageCount = ceil ($catesCount / $pageSize );
		$this->assign('pageCount',$pageCount);
		$news = db('u_news u')->join('member m','u.uid=m.uid')->where($where)->order('u.news_id desc')->limit($pageStart,$pageSize)->select();
		foreach ($news as $key => $value) {
			$comments = db('u_comment u')->field('u.*,m.uname,m.u_face')->join('member m','m.uid=u.uid')->where(['news_id' => $value['news_id'],'u.closed'=>0,'m.closed'=>0])->order('u.dateline desc')->limit(2)->select();
			$news[$key]['comments'] = $comments;
		}
		$this->assign('news',$news);
		$hots = $news = db('u_news')->order('news_id desc')->limit(8)->select();
		$this->assign('hots',$hots);
		return $this->fetch();
	}

	public function detail($news_id =''){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数错误!');
		}

		$news = db('u_news u')
		->field('u.*,m.uname,m.u_face')
		->join('member m','m.uid=u.uid')
		->where(array('u.closed'=>0,'news_id'=>$news_id))
		->find();
		if ($news == false) {
			alert_output('文章审核中或不存在!');
		}

		$this->assign('news',$news);
		$hots = $news = db('u_news')->order('news_id desc')->order('dateline desc')->limit(8)->select();
		$this->assign('hots',$hots);
		$pageSize = 5;
		$page = input('?get.page') ? input('get.page') : 1;
		$this->assign('page',$page);
		$pageStart = ($page -1)*$pageSize;
		$catesCount =  db('u_comment u')->field('u.*,m.uname,m.u_face')->join('member m','m.uid=u.uid')->where(['news_id' => $news_id,'u.closed'=>0,'m.closed'=>0])->count();
		$pageCount = ceil ($catesCount / $pageSize );
		$this->assign('pageCount',$pageCount);

		$comments = db('u_comment u')->field('u.*,m.uname,m.u_face')->join('member m','m.uid=u.uid')->where(['news_id' => $news_id,'u.closed'=>0,'m.closed'=>0])->order('u.dateline desc')->limit($pageStart,$pageSize)->select();
		$this->assign('comments',$comments);
		return $this->fetch();
	}

	public function addcomment(){
		if (!$data = input('post.')) {
			alert_output('参数错误');
		}
		$data['ip'] = get_real_ip();
		$data['dateline'] = time();
		$data['uid'] = session('uid');
		$data['closed'] = 2;
		if (db('u_comment')->insert($data)) {
			alert_output('添加成功!');
		}else{
			alert_output('服务器异常,请稍后再试!');
		}

	}



}