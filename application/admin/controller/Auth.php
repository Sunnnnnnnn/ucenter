<?php
namespace app\admin\controller;
use app\admin\model\Admin;


class Auth extends \think\Controller
{
    public function login() {
        
        $usr = input('post.username');
        $pwd = input('post.password');
        if($usr && $pwd) {
            /*if(!captcha_check(input('captcha'))) {
                $this -> error('验证码错误！', 'login');
            };*/
            $admin = new Admin();
            if ($rule = $admin->checkUser($usr,$pwd)) {
                session('admin_login',true);
                session('admin_username',$usr);
                session('admin_rule',$rule);

                $this -> redirect('user/index');
            } else {
                $this -> error('username or password error!', 'login');
            }
        } else {
            return $this -> fetch();
        }
        
    }

    public function logout() {
        session('admin_login',null);
        session('admin_username',null);
        $this -> redirect('Auth/login');
    }

}
