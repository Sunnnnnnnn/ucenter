<?php
namespace app\index\controller;
use app\index\model\Member;
use app\index\model\Sms;


class Auth extends \think\Controller
{
    public function login() {
        
        $usr = input('post.username');
        $pwd = input('post.password');
        $p = input('post.password');
        if($usr && $pwd) {
            /*if(!captcha_check(input('captcha'))) {
                alert_output('验证码不正确!');
            };*/
            $member = new Member();
            if ($uid = $member->checkUser($usr,$pwd)) {
                session('uid',$uid);
                alert_output('登录成功!',get_member());

            } else {
                alert_output('用户名密码不正确!');
            }
        } 
        
    }

    public function phonelogin(){
        $phone = input('post.phone');
        $phone_captcha = input('post.phone_captcha');
        if($phone) {
            //短信验证
            $code = session('code_'.$phone);
            if (!$code) {
                alert_output('短信验证码不能为空!');
            }
            $code = explode('_', $code);
            if (is_array($code)) {
                if (time() - 300 > $code[1]) {
                    session('code_'.$phone,null);
                    alert_output('验证码失效!');
                }
                if ($phone_captcha != $code[0]) {
                    alert_output('短信验证码不正确!');
                }
            }else{
                alert_output('短信验证码不正确');
            }
            $member = new Member();
            if ($uid = $member->checkUserByPhone($phone)) {
                session('uid',$uid);
                alert_output('登录成功!',get_index());
            } else {
                alert_output('用户名密码不正确!');
            }
        } else{
            alert_output('手机号码不能为空!');
        }
    }

    //发送短信验证码  1 确认登录 2 注册 3 修改密码
    public function sendsms()
    {

        $phone= input("?post.phone") ? input("post.phone") :'';
        $type= input("?post.type") ? input("post.type") :'';
 
        if(!preg_match("/^1[34578]\d{9}$/", $phone)){
            json_output(101,'手机号不正确！！',array($phone));
        }
        $code = rand(100000,999999);
        session('code_'.$phone,$code.'_'.time());
        $sms = new Sms();
        if($sms->sendSms($phone,$type,$code)){
            json_output(200,'发送成功!',array($phone));
        }
    }

    public function sendtest(){
        $sms = new Sms();
        $phone = '13125131037';
        if($sms->sendSms($phone,1,11)){
            json_output(200,'发送成功!',array($phone));
        }
    }

    public function logout() {
        session('uid',null);
        $this -> redirect('/index.php/index/index');
    }


    public function register()
    {
        $phone = input('post.phone');
        $phone_captcha = input('post.phone_captcha');
        if($phone) {
            //短信验证
            $code = session('code_'.$phone);
            if (!$code) {
                alert_output('短信验证码不能为空!');
            }
            $code = explode('_', $code);
            if (is_array($code)) {
                if (time() - 300 > $code[1]) {
                    session('code_'.$phone,null);
                    alert_output('验证码失效!');
                }
                if ($phone_captcha != $code[0]) {
                    alert_output('短信验证码不正确!');
                }
            }else{
                alert_output('短信验证码不正确');
            }
            if (db('member')->where(array('mobile' => $phone))->find()) {
                alert_output('手机号已被注册!');
            }
            $password = 'ht'.rand(100000,999999);
            $data = array(
                'uname' => $phone,
                'mobile' => $phone,
                'passwd' => md5($password),
                'regip' => get_real_ip(),
                'dateline' => time()
                );
            $res = db('member')->insert($data);
            $sms = new Sms();
            $sms->sendSms($phone,4,$password);
            if ($res) {
                alert_output('注册成功!',get_index());
            } else {
                alert_output('服务器异常,请稍后再试!');
            }
        }else{
            alert_output('手机号码不能为空!');
        }
    }

    public function changePass(){
        $phone = input('get.phone');
        $phone_captcha = input('get.phone_captcha');
        $passwd = input('get.passwd');
        if($phone) {
            //短信验证
            $code = session('code_'.$phone);
            if (!$code) {
                alert_output('短信验证码不能为空!');
            }
            $code = explode('_', $code);
            if (is_array($code)) {
                if (time() - 300 > $code[1]) {
                    session('code_'.$phone,null);
                    alert_output('验证码失效!');
                }
                if ($phone_captcha != $code[0]) {
                    alert_output('短信验证码不正确!');
                }
            }else{
                alert_output('短信验证码不正确');
            }
            $user = db('member')->where(array('mobile' => $phone))->find();
            if (!$user) {
                alert_output('手机号码未注册!');
            }
            
            $res = db('member')->where(array('mobile' =>$phone))->update(array('passwd' =>md5($passwd)));
            
            if ($res) {
                alert_output('修改成功!',get_index());
            } else {
                alert_output('服务器异常,请稍后再试!');
            }
        }else{
            alert_output('手机号码不能为空!');
        }
    }

    public function isLogin()
    {
        json_output(200,'',session('?uid') ? 1 :0);
    }

}
