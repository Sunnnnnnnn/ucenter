<?php
namespace app\admin\controller;

class User extends Base
{

    public $pageSize = 25;

    public function index()
    {
        $search = input('post.search');
        if ($search!= false) {
            $list = db("member")
            ->where('uname|uid','like','%'.$search.'%')
            ->where(array(
                'closed' => 0,
                'u_face' =>array('neq','')
                ))
            ->order("uid DESC")
            ->paginate($this->pageSize,false,array(
                ));
        }else{
            $list = db("member")
            ->where(array(
                'closed' => 0,
                'u_face' =>array('neq','')
                ))
            ->order("uid DESC")
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

    public function edit($uid = false){
        if ($uid == false || $uid != (string)(int)$uid) {
            $this->error('参数异常！','user/index');
        }
        $data = input('post.');
        if ($data) {
            if ($data['birthday']) {
                $data['Y'] = date('Y',strtotime($data['birthday']));
                $data['M'] = date('m',strtotime($data['birthday']));
                $data['D'] = date('d',strtotime($data['birthday']));
            }
            unset($data['birthday']);
            if ($data['verify_mobile'] || $data['verify_mail']) {
                $data['verify'] = 0;
                $data['verify'] += $data['verify_mobile'] ?2 : 0;
                $data['verify'] += $data['verify_mail'] ?1 : 0;
            }
            unset($data['verify_mail']);
            unset($data['verify_mobile']);
            $thumb = uploadFile('u_face','user'); 
            if ($thumb) {
                $data['u_face'] = $thumb['img'];
            }
            if ($data['point']||$data['level']||$data['leveldateline']) {
                if(db('u_member_field')->where(['uid'=>$uid])->find()){
                    db('u_member_field')->where(['uid'=>$uid])->update(['point'=>$data['point'],'level'=>$data['level'],'paydate'=>strtotime($data['leveldateline'])]);
                }else{
                    db('u_member_field')->insert(['uid'=>$uid,'point'=>$data['point'],'level'=>$data['level'],'paydate'=>strtotime($data['leveldateline'])]);
                }
            }
            unset($data['point']);
            unset($data['level']);
            unset($data['leveldateline']);

            $res = db('member')->where(array('uid' =>$uid))->update($data);
            if ($res !== false) {
                $this->success('修改成功！');
            }else{
                $this->error('服务器异常！','user/index');
            }
        }else{
            $user = db('member')->where(array('uid' =>$uid))->find();
            $birthday = $user['Y'].'-'.$user['M'].'-'.$user['D'];
            $user['birthday'] = date('Y-m-d',strtotime($birthday));
            if ($user['verify']) {
                $verify = $user['verify'] - 2;
                $user['verify_mobile'] = $verify < 0 ? 0 :1;
                $user['verify_mail'] = $verify;
            }else{
                $user['verify_mobile'] = 0;
                $user['verify_mail'] = 0;
            }
            $user['level'] = db('u_member_field')->where(['uid'=>$uid])->find()['level'];
            $user_field = db('u_member_field')->where(['uid'=>$uid])->find();
            $user['point'] = $user_field['point'];
            $user['paydate'] = $user_field['paydate'];
            $this->assign('user',$user);
            return $this->fetch();
        }

    }

    public function add(){
        $data = input('post.');
        if ($data) {
            if(db('member') -> where(array('uname' =>$data['uname']))->find()){
                $this->error('用户名重复!');
            }
            $data['passwd'] = md5($data['passwd']);
            if ($data['birthday']) {
                $data['Y'] = date('Y',strtotime($data['birthday']));
                $data['M'] = date('m',strtotime($data['birthday']));
                $data['D'] = date('d',strtotime($data['birthday']));
            }
            unset($data['birthday']);
            if ($data['verify_mobile'] || $data['verify_mail']) {
                $data['verify'] = 0;
                $data['verify'] += $data['verify_mobile'] ?2 : 0;
                $data['verify'] += $data['verify_mail'] ?1 : 0;
            }
            unset($data['verify_mail']);
            unset($data['verify_mobile']);
            $data['dateline'] = time();
            $data['from'] = 'shop';
            $thumb = uploadFile('u_face','user'); 
            if ($thumb) {
                $data['u_face'] = $thumb['img'];
            }
            if ($data['point']||$data['level']||$data['leveldateline']) {
                $temp = ['point'=>$data['point'],'level'=>$data['level'],'paydate'=>strtotime($data['leveldateline'])];
            }
            unset($data['point']);
            unset($data['level']);
            unset($data['leveldateline']);
            if ($uid = db('member')->insertGetId($data)) {
                $temp['uid'] = $uid;
                db('u_member_field')->insert($temp);
                $this->success('新增用户信息成功！');
            }else{
                $this->error('服务器异常！','user/index');
            }
        }else{
            return $this->fetch();
        }
    }

    public function delete($uid){
        if ($uid == false || $uid != (string)(int)$uid) {
            $this->error('参数异常！','user/index');
        }

        if (db('member')->where(array('uid' =>$uid))->update(array('closed'=>1))) {
            $this->success('删除资料成功！');
        }else{
            $this->error('服务器异常！','user/index');
        }
    }

}
