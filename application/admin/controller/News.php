<?php
namespace app\admin\controller;

class News extends Base
{
	public $pageSize = 25;

	public function index($closed = '')
	{
		$search = input('post.search');
		$where = array();
		if ($closed == (string)(int)$closed) {
            $where['closed'] = $closed; 
        }else{
            $where['closed'] =['neq',1];
        }
		if ($search!= false) {
			$list = db("u_news")
			->where('title|news_id','like','%'.$search.'%')
			->where($where)
			->order("news_id DESC")
			->paginate($this->pageSize,false,array(
				));
		}else{
			$list = db("u_news")
			->where($where)
			->order("news_id DESC")
			->paginate($this->pageSize,false,array(
				));
		}

		$this->assign('search',$search);
		$this->assign('closed',$closed);
    		//  总数据
		$this->assign('total',$list->total());
    		//  总页数
		$total= ceil($list->total() / $this->pageSize);
		$this->assign('totalPage', $total);
    		//
		$this->assign('list',$list);

		return $this->fetch();
	}

	public function edit($news_id = false){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数异常！','news/index');
		}
		$data = input('post.');
		if ($data) {
			$data['dateline'] = time();
			$data['closed'] = 0;
			$thumb = uploadFile('cover','news'); 
			if ($thumb) {
				$data['cover'] = $thumb['img'];
			}
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

	}

	public function add(){

		$data = input('post.');
		if ($data) {
			if ($data['uid'] == false) {
				$this->error('用户id不能为空','news/index');
			}
			if (!db('member')->where(['uid' => $data['uid'],'closed'=>0])->find()) {
				$this->error('用户不存在','news/index');
			}
			$data['dateline'] = time();
			$thumb = uploadFile('cover','news'); 
			if ($thumb) {
				$data['cover'] = $thumb['img'];
			}

			if (db('u_news')->insert($data)) {
				$this->success('添加成功！','news/index');
			}else{
				$this->error('服务器异常！','news/index');
			}
		}else{
			
			return $this->fetch();
		}
	}

	public function delete($news_id){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数异常！','news/index');
		}

		if (db('u_news')->where(array('news_id' =>$news_id))->update(array('closed'=>1))) {
			$this->success('删除成功！');
		}else{
			$this->error('服务器异常！','news/index');
		}
	}

	public function setClosed($news_id,$value = 0){
		if ($news_id == false || $news_id != (string)(int)$news_id) {
			$this->error('参数异常！','news/index');
		}

		if (db('u_news')->where(array('news_id' =>$news_id))->update(array('closed'=>$value)) !== false) {
			$this->success('操作成功！');
		}else{
			$this->error('服务器异常！','news/index');
		}
	}

}
