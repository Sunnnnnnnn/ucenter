<?php
namespace app\admin\model;

class Admin extends \think\Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'jh_admin';
    // 设置当前模型的数据库连接
    
    
    // 获取所有用户的所有数据
    public function getAllUserDatas()
    {
        $more_datas = $this->select();          // 查询所有用户的所有字段资料
        if (empty($more_datas)) {                 // 判断是否出错
            return false;
        }
        return tp5ModelTransfer($more_datas);   // 返回修改后的数据
    }

    // 获取所有用户的所有数据
    public function checkUser($admin_name,$passwd)
    {
        $more_datas = $this->field('role_id')->where(array('admin_name' => $admin_name,'passwd' => md5($passwd)))->select();          // 查询所有用户的所有字段资料
        if (empty($more_datas)) {                 // 判断是否出错
            return false;
        }
        return tp5ModelTransfer($more_datas)[0]['role_id'];   // 返回修改后的数据
    }



}
