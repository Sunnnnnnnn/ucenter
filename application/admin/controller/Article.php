<?php
namespace app\admin\controller;

class Article extends Base
{
	public $pageSize = 25;

    public function index()
    {
        $search = input('post.search');
        if ($search!= false) {
            $list = db("u_article")
            ->where('title|article_id','like','%'.$search.'%')
            ->where(array(
                'closed' => 0
                ))
            ->order("article_id DESC")
            ->paginate($this->pageSize,false,array(
                ));
        }else{
            $list = db("u_article")
            ->where(array(
                'closed' => 0
                ))
            ->order("article_id DESC")
            ->paginate($this->pageSize,false,array(
                ));
        }
        
        $this->assign('search',$search);
		//  总数据
        $this->assign('total',$list->total());
		//  总页数
        $total= ceil($list->total() / $this->pageSize);
        $this->assign('totalPage', $total);
		//
        $this->assign('list',$list);
        return $this->fetch();

    }

    public function edit($article_id = false){
    	if ($article_id == false || $article_id != (string)(int)$article_id) {
    		$this->error('参数异常！','article/index');
    	}
    	$data = input('post.');
    	if ($data) {
            if(db('u_article') ->where('article_id','neq',$article_id)-> where(array('title' =>$data['title']))->where('article_id','neq',$article_id)->find()){
                $this->error('文章标题重复!');
            }
            $data['dateline'] = time();
            $data['closed'] = 0;
            $thumb = uploadFile('cover','article'); 
            if ($thumb) {
             $data['cover'] = $thumb['img'];
         }
         $res = db('u_article')->where(array('article_id' =>$article_id))->update($data);

         if ($res != false) {
             $this->success('修改成功！');
         }else{
             $this->error('服务器异常！','article/index');
         }
     }else{
        $cates = $this->getCates(3);
        $this->assign('cates',$cates);
        $article = db('u_article')->where(array('article_id' =>$article_id))->find();
        $this->assign('article',$article);
        return $this->fetch();
    }
    
}

public function add(){
 $data = input('post.');
 if ($data) {
    if(db('u_article') -> where(array('title' =>$data['title']))->find()){
        $this->error('文章标题重复!');
    }
    $data['dateline'] = time();
    $data['closed'] = 0;
    $thumb = uploadFile('cover','article'); 
    if ($thumb) {
       $data['cover'] = $thumb['img'];
   }

   if (db('u_article')->insert($data)) {
       $this->success('添加成功！','article/index');
   }else{
       $this->error('服务器异常！','article/index');
   }
}else{
  $cates = $this->getCates(3);
  $this->assign('cates',$cates);
  return $this->fetch();
}
}

public function delete($article_id){
  if ($article_id == false || $article_id != (string)(int)$article_id) {
      $this->error('参数异常！','article/index');
  }

  if (db('u_article')->where(array('article_id' =>$article_id))->update(array('closed'=>1))) {
      $this->success('修改成功！');
  }else{
      $this->error('服务器异常！','article/index');
  }
}





}
