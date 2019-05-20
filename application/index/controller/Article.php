<?php 
namespace app\index\controller;

class Article extends Base
{
	
	public function index($cate_id = 4){
		$where = array('closed' => 0);
		if ($cate_id == (string)(int)$cate_id) {
			$where['cate_id'] = $cate_id;
		}
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>3))->select();
		$this->assign('cates',$cates);

		$pageSize = 9;
		$page = input('?get.page') ? input('get.page') : 1;
		$this->assign('page',$page);
		$pageStart = ($page -1)*$pageSize;
		$catesCount = db('u_article') ->where($where)->count();
		$pageCount = ceil ($catesCount / $pageSize );
		$this->assign('pageCount',$pageCount);
		$articles = db('u_article')->where($where)->limit($pageStart,$pageSize)->select();
		if ($articles) {
          foreach ($articles as $key => $value) {
            $articles[$key]['content'] =mb_substr(strip_tags($articles[$key]['content']),0,300,'utf-8');
          }
        }
		$this->assign('articles',$articles);
		
		return $this->fetch();
	}

	public function detail($article_id =''){
		if (!$article = db('u_article')->where(array('closed'=>0,'article_id'=>$article_id))->find()) {
			alert_output('文章不存在',get_index('article/index'));
		}
		db('u_article')->where(array('article_id' =>$article_id)) ->update(array('views'=>$article['views']+1));

		$next = db('u_article')->where(array('closed'=>0,'cate_id'=>$article['cate_id']))->where('article_id','<',$article_id)->order('article_id desc')->find();
		$this->assign('next',$next);
		$this->assign('article',$article);
		//热门推荐
		$hots = db('u_article')->where(array('closed' => 0))->limit(6)->order('article_id desc')->select();
		$this->assign('hots',$hots);
		//分类
		$cates = db('u_cate') ->where(array('closed'=>0,'parent_id'=>3))->select();
		$this->assign('cates',$cates);
		return $this->fetch();
	}


}