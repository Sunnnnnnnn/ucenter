<?php
namespace app\index\controller;

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
      
        if (session('?uid')) {
            $uid =session('uid');
            $user = db('member')->where(array('uid' => $uid,'closed' => 0))->find();

            $arr = ['注册会员','铜牌会员','银牌会员','金牌会员','钻石会员'];
            $user['level'] = $arr[$this->getUserLevel($uid)];
            $user_field = db('u_member_field')->where(['uid'=>$uid])->find();
            $user['point'] = $user_field['point'];
            $user['leveldate'] = $user_field['paydate'] > time() ? $user_field['paydate'] : 0;


            $this->assign('user_info',$user);
        }

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
