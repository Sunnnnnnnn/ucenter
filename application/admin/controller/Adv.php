<?php
namespace app\admin\controller;

class Adv extends Base
{
  public $pageSize = 25;

  public function index()
  {
    $search = input('post.search');
    if ($search!= false) {
      $list = db("u_adv")
      ->where('title|adv_id','like','%'.$search.'%')
      ->where(array(
        'closed' => 0
        ))
      ->order("adv_id DESC")
      ->paginate($this->pageSize,false,array(
        ));
  }else{
      $list = db("u_adv")
      ->where(array(
        'closed' => 0
        ))
      ->order("adv_id DESC")
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

public function edit($adv_id = false){
    if ($adv_id == false || $adv_id != (string)(int)$adv_id) {
      $this->error('参数异常！','adv/index');
  }
  $data = input('post.');
  if ($data) {
      $data['dateline'] = time();
      $data['closed'] = 0;
      $thumb = uploadFile('cover','adv'); 
      if ($thumb) {
        $data['cover'] = $thumb['img'];
    }
    $res = db('u_adv')->where(array('adv_id' =>$adv_id))->update($data);

    if ($res != false) {
        $this->success('修改成功！');
    }else{
        $this->error('服务器异常！','adv/index');
    }
}else{
  $adv = db('u_adv')->where(array('adv_id' =>$adv_id))->find();
  $this->assign('adv',$adv);
  $cates = $this->getCates(12);
  $this->assign('cates',$cates);
  return $this->fetch();
}

}

public function add(){

    $data = input('post.');
    if ($data) {
      $data['dateline'] = time();
      $data['closed'] = 0;
      $thumb = uploadFile('cover','adv'); 
      if ($thumb) {
        $data['cover'] = $thumb['img'];
    }

    if (db('u_adv')->insert($data)) {
        $this->success('添加成功！','adv/index');
    }else{
        $this->error('服务器异常！','adv/index');
    }
}else{
  $cates = $this->getCates(12);
  $this->assign('cates',$cates);
  return $this->fetch();
}
}

public function delete($adv_id){
    if ($adv_id == false || $adv_id != (string)(int)$adv_id) {
      $this->error('参数异常！','adv/index');
  }

  if (db('u_adv')->where(array('adv_id' =>$adv_id))->delete()) {
      $this->success('删除成功！');
  }else{
      $this->error('服务器异常！','adv/index');
  }
}





}
