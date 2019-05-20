<?php
namespace app\admin\controller;

class Base extends \think\Controller
{
// 分页参数 默认25

    protected $_getPageSize = 25;

    function __construct() {
        parent::__construct();

        header("Content-Type:text/html;charset=utf-8");
        date_default_timezone_set('Asia/Shanghai');
        $this->assign('null',null);
    }


    public function _initialize(){
//登录检查
        if (session('admin_login') != true) {
            $this -> redirect('Auth/login');
        }

    }





//是否有权限操作
    protected function haveRule($rule){
        if (session('rule') < $rule) 
            $this->error('没有权限',2);
    }

//获取分类列表
    public function getCates($parent_id = ''){
        $where = array('closed' => 0);
        if ($parent_id) {
            $where['parent_id'] = $parent_id;
        }
        return db('u_cate')->where($where)->select();
    }

    //获取用户级别
    public function getUserLevel($uid){
        $data = db('u_member_field')->where(['uid'=>$uid])->find();
        $level = 0;
        if ($data) {
            $temp = 0;
            if ($data['level'] != false && $data['paydate'] != false && $data['paydate'] > time()) {
                $temp = $data['level'];
            }
            /*     
            注册会员    注册即成为会员 0       

            铜牌会员    成长值≥2000    1   9.8折
            银牌会员    成长值≥10000   2   9.5折
            金牌会员    成长值≥30000   3   9折
            钻石会员    成长值≥80000   4   8折
             */
            if ($data['point'] >= 80000) {
                $level = 4;
            }elseif ($data['point'] >= 30000) {
                $level = 3;
            }elseif ($data['point'] >= 10000) {
                $level = 2;
            }elseif ($data['point'] >= 2000) {
                $level = 1;
            }else{
                $level = 0;
            }
            if ($temp > $level) {
                $level = $temp;
            }

        }
        return $level;
    }

    //获取用户优惠
    public function getUserDiscount($uid){
        $level = $this->getUserLevel($uid);
        $arr = [1,0.98,0.95,0.9,0.8];
        return $arr[$level];
    }
    //
    public function getUserPoint($uid){
        $data = db('u_member_field')->where(['uid'=>$uid])->find();
        if ($data) {
            return $data['point'];
        }
        return 0;
    }
}
