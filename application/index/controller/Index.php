<?php
namespace app\index\controller;
use app\index\model\Member;

class Index extends Base
{
    public function index()
    {
        //广告
        $advs = db('u_adv')->where(array('closed' => 0))->limit(3)->select();
        $this->assign('advs',$advs);
       	$data = array();
       	$data['news'] = db('u_article')->where(array('closed'=>0,'cate_id'=>4))->order('article_id desc')->limit(7)->select();
        if ($data['news']) {
          foreach ($data['news'] as $key => $value) {
            $data['news'][$key]['content'] =mb_substr(strip_tags($data['news'][$key]['content']),0,35,'utf-8');
          }
        }
       	$data['news_first'] = $data['news'][0];
       	unset($data['news'][0]);
       	$data['helps'] = db('u_article')->where(array('closed'=>0,'cate_id'=>5))->order('article_id desc')->limit(7)->select();
        if ($data['helps']) {
          foreach ($data['helps'] as $key => $value) {
            $data['helps'][$key]['content'] =mb_substr(strip_tags($data['helps'][$key]['content']),0,35,'utf-8');
          }
        }
       	$data['helps_first'] = $data['helps'][0];
       	unset($data['helps'][0]);
       	//商品
        $cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
       	$data['products'] = db('u_product')->where($where)->limit(16)->select();
       	//专家 //
        $cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
       	$data['experts'] = db('u_product') -> where($where)->limit(5)->select();
       	$this->assign('data',$data);
		return $this->fetch();
    }

    public function test(){
    	$uname=input('post.name');
    	echo $uname;
    	return $this->fetch();
    }
}
