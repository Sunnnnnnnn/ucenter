<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


function tp5ModelTransfer($data)
{
    if (empty($data) || !count($data)) {
        return false;
    }
    foreach ($data as $value) {
        $datarray[] = $value->toArray();
    }
    return $datarray;
}
//上传图片
function uploadFile($name,$filePath,$width = 0,$height = 0)
{
    $file = request()->file($name);
    if($file){
        $filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
        if(!file_exists($filePaths)){
            mkdir($filePaths,0777,true);
        }
        $info = $file->move($filePaths);
        if($info){
            $defpath = 'public/uploads/';
            $imgpath = $filePath.'/'.str_replace('\\', '/', $info->getSaveName());
            if ($width && $height) {
                $image = \think\Image::open($defpath.$imgpath);
                $date_path = $filePath.'/thumb/'.date('Ymd');
                if(!file_exists($defpath.$date_path)){
                    mkdir($defpath.$date_path,0777,true);
                }
                $thumb_path = $date_path.'/'.$info->getFilename();
                $image->thumb($width, $height)->save($defpath.$thumb_path);
                $data['thumb_img'] = $thumb_path;
            }
            $data['img'] = $imgpath;
            return $data;
        }else{
// 上传失败获取错误信息
            return $file->getError();
        }
    }
}

function get_img_url($str = false){
    return '/public' . DS . 'uploads' . DS . $str;
}



function get_base_url($absolute=true){
    $baseUrl=rtrim(dirname(get_script_url()),'\\/');
    return $absolute ? get_hostinfo() . $baseUrl : $baseUrl;
}

/**
* 获取当前主机
* @return string 主机字符串
*/
function get_hostinfo(){
    if(!empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off'))
        $http='https';
    else
        $http='http';
    if(isset($_SERVER['HTTP_HOST']))
        $hostInfo=$http.'://'.$_SERVER['HTTP_HOST'];
    else
    {
        $hostInfo=$http.'://'.$_SERVER['SERVER_NAME'];
        $port=isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        if($port!==80)
            $hostInfo.=':'.$port;
    }
    return $hostInfo;
}

function get_url(){
    return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
}

function get_index($str = '',$param = array()){
    if (is_array($param) && !empty($param)) {
        $temp = '';
        foreach ($param as $key => $value) {
            $temp .= '/'.$key.'/'.$value;
        }
        $str .= $temp;

    }
    if ($str) {
        $str = '/'.$str;
    }
    return get_hostinfo().'/index.php'.$str;
}

function get_admin($str = '',$param = array()){
    if (is_array($param) && !empty($param)) {
        $temp = '';
        foreach ($param as $key => $value) {
            $temp .= '/'.$key.'/'.$value;
        }
        $str .= $temp;
    }
    if ($str) {
        $str = '/'.$str;
    }
    return get_hostinfo().'/admin.php'.$str;
}

function get_member($str = '',$param = array()){
    if (is_array($param) && !empty($param)) {
        $temp = '';
        foreach ($param as $key => $value) {
            $temp .= '/'.$key.'/'.$value;
        }
        $str .= $temp;
    }
    if ($str) {
        $str = '/'.$str;
    }
    return get_hostinfo().'/member.php'.$str;
}

function get_public($str = false){
    return get_hostinfo().'/public/'.$str;
}



/**
* 当前脚本url
* @return [type] [description]
*/
function get_script_url(){
    $scriptName=basename($_SERVER['SCRIPT_FILENAME']);
    if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
        $scriptUrl=$_SERVER['SCRIPT_NAME'];
    elseif(basename($_SERVER['PHP_SELF'])===$scriptName)
        $scriptUrl=$_SERVER['PHP_SELF'];
    elseif(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
        $scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
    elseif(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
        $scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
    elseif(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
        $scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
    else
        die("ERROR");
    return $scriptUrl;
}

function get_uploads($str = false) {
    return get_base_url() . '/public/uploads/'.$str;
}

function get_adminUser_name() {
    return session('username');
}

function get_adminUser_duty(){
    return session('duty');
}

function get_adminUser_rule(){
    return session('rule');
}

/*
*按综合方式输出数据
**/
function show_output($code, $message = '', $data = array(),$from='', $type = '', $cb = '') {
    if ((!isset($type) || empty($type)) && isset($_GET['format'])) {
        $type = $_GET['format'];
    }
    if ($code !== 200) {
        \think\Log::error("code:" . $code . "\nmessage:" . $message . "\nparams:" . getCurrentPagePostParams(). "\nfrom:" .$from );
    }
    if ($data != false) {
        foreach ($data as $key => $value) {
            if (array_key_exists('content',$value)) {
                $data[$key]['content'] = substr_num($value['content'],120);
            }
            if (array_key_exists('cover', $data[$key])) {
                $data[$key]['cover'] = get_uploads($value['cover']);
            }

        }
    }
    if ($type == "xml") {
        xml_output($code, $message, $data);
    } else if ($type === "jsonp") {
        jsonp_output($code, $message, $data, $cb);
    } else {
        json_output($code, $message, $data);
    }
}

function getCurrentPagePostParams() {
    $result = "";
    foreach ($_POST as $param_name => $param_val) {
        $result .= "       $param_name:$param_val\n";
    }
    return $result;
}

function jsonp_output($code, $message = "", $data = array(), $cb) {
    if (!is_numeric($code)) {
        $code = 101;
        $message = '状态码有误';
    }
    $response = array(
        "code" => $code,
        "message" => $message,
        "data" => $data
        );
    header('Content-type: application/json');
    echo $cb . '(' . json_encode($response) . ');';
    exit();
}

/*
**/
function json_output($code, $message = '', $data = array()) {
    if (!is_numeric($code)) {
        $code = 101;
        $message = '状态码有误';
    }
    $response = array(
        "code" => $code,
        "message" => $message,
        "data" => $data
        );
    header('Content-type: application/json');
    echo json_encode($response);
    exit();
}

/*
*按XML方式输出数据
**/
function xml_output($code, $message = '', $data = array()) {
    if (!is_numeric($code)) {
        $code = 101;
        $message = '状态码有误';
    }
    $response = array(
        "code" => $code,
        "message" => $message,
        "data" => $data
        );
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
    $xml .= '<root>';
    $xml .= array2xml($response);
    $xml .= '</root>';
    header('Content-type: text/xml');
    echo $xml;
    exit();
}

/*
*alert 弹出层
**/
function alert_output($message = '', $href = '') {
    if ($href == false) {
        $response = "<script>alert('$message');window.history.back(-1);</script>";
    }else{
        $response = "<script>alert('$message');window.location.href='$href';</script>";
    }
    header("Content-type: text/html; charset=utf-8"); 
    echo $response;
    exit();
}

function get_real_ip(){  
    $ip=false;  
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){  
        $ip = $_SERVER["HTTP_CLIENT_IP"];  
    }  
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);  
        if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }  
        for ($i = 0; $i < count($ips); $i++) {  
            if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {  
                $ip = $ips[$i];  
                break;  
            }  
        }  
    }  
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);  
}  
function get_cate_name($cate_id =''){

    return db('u_cate')->where(array('closed'=>0,'cate_id' => $cate_id))->find()['name'];;
}

function get_last_cate($cate_id =''){
    $cates = [];
    $c1 = db('u_cate')->where(['parent_id' =>$cate_id])->select();
    if ($c1) {
        foreach ($c1 as $cv1) {
            $c2 = db('u_cate')->where(['parent_id' =>$cv1['cate_id']])->select();
            if ($c2) {
                foreach ($c2 as $cv2) {
                    $c3 = db('u_cate')->where(['parent_id' =>$cv2['cate_id']])->select();
                    if ($c3) {
                        foreach ($c3 as $cv3) {
                            $cates[] = $cv3;
                        }
                    }else{
                        $cates[] = $cv2;
                    }
                }
            }else{
                $cates[] = $cv1;
            }
        }
    }
    return $cates;
}   

function get_article_type_name($type =''){
    $types = array(
        1 => '新闻' ,
        2=> '帮助'
        );
    return $types[$type];
}

function get_user_discount($uid){
    $base = new app\admin\controller\Base;
    return $base->getUserDiscount($uid);
}

function get_user_level($uid){
    $base = new app\admin\controller\Base;
    return $base->getUserLevel($uid);
}

function array_key_value($arr,$k){
    $temp = [];
    if (is_array($arr)) {
        foreach ($arr as $key => $value) {
            if (array_key_exists($k, $value)) {
                $temp[] = $value[$k];
            }
        }
    }else{
        $temp = $arr;
    }

    return $temp;
}

function get_cate_id_by_order_id($order_id){
    return  db('u_order_content u')->join('u_product p','p.product_id=u.product_id')->where(['order_id' =>$order_id])->find()['cate_id'];

}
