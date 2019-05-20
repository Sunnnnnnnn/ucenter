<?php
namespace app\admin\controller;

class Comment extends Base
{
    public $pageSize = 25;

    public function index($closed = '')
    {
        $where = array();
        if ($closed == (string)(int)$closed) {
            $where['closed'] = $closed; 
        }else{
            $where['closed'] = ['neq',1];
        }
        
        $list = db("u_comment")
        ->where($where)
        ->order("comment_id DESC")
        ->paginate($this->pageSize,false,array(
            ));
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

    public function edit($comment_id = false){
        if ($comment_id == false || $comment_id != (string)(int)$comment_id) {
            $this->error('参数异常！','comment/index');
        }
        $data = input('post.');
        if ($data) {
            $data['dateline'] = time();
            $data['closed'] = 0;
            $thumb = uploadFile('cover','comment'); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
            }
            $res = db('u_comment')->where(array('comment_id' =>$comment_id))->update($data);

            if ($res != false) {
                $this->success('修改成功！');
            }else{
                $this->error('服务器异常！','comment/index');
            }
        }else{

            $comment = db('u_comment')->where(array('comment_id' =>$comment_id))->find();
            $this->assign('comment',$comment);
            return $this->fetch();
        }

    }

    public function add(){

        $data = input('post.');
        if ($data) {
            if ($data['uid'] == false) {
                $this->error('用户id不能为空','comment/index');
            }
            if (!db('member')->where(['uid' => $data['uid'],'closed'=>0])->find()) {
                $this->error('用户不存在','comment/index');
            }
            $data['dateline'] = time();
            $thumb = uploadFile('cover','comment'); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
            }

            if (db('u_comment')->insert($data)) {
                $this->success('添加成功！','comment/index');
            }else{
                $this->error('服务器异常！','comment/index');
            }
        }else{
            
            return $this->fetch();
        }
    }

    public function delete($comment_id){
        if ($comment_id == false || $comment_id != (string)(int)$comment_id) {
            $this->error('参数异常！','comment/index');
        }

        if (db('u_comment')->where(array('comment_id' =>$comment_id))->update(array('closed'=>1))) {
            $this->success('删除成功！');
        }else{
            $this->error('服务器异常！','comment/index');
        }
    }

    public function setClosed($comment_id,$value = 0){
        if ($comment_id == false || $comment_id != (string)(int)$comment_id) {
            $this->error('参数异常！','comment/index');
        }

        if (db('u_comment')->where(array('comment_id' =>$comment_id))->update(array('closed'=>$value)) !== false) {
            $this->success('操作成功！');
        }else{
            $this->error('服务器异常！','comment/index');
        }
    }

}
