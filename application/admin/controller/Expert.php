<?php
namespace app\admin\controller;

class Expert extends Base
{
    public $pageSize = 25;

    public function index($province_id = '')
    {
        
        $cates = get_last_cate(2);
        $cate_ids = array_key_value($cates,'cate_id');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
        if ($province_id !=false && $province_id == (string)(int)$province_id) {
            $where['p.province_id'] = $province_id;
        }
        $this->assign('province_id',$province_id);
        $search = input('post.search');
        if ($search!= false) {
            $list = db("u_product p")
            ->field('p.*,d.province_name')
            ->join('data_province d','d.province_id=p.province_id')
            ->where('name|product_id','like','%'.$search.'%')
            ->where($where)
            ->order("product_id DESC")
            ->paginate($this->pageSize,false,array(
                ));
        }else{
            $list = db("u_product p")
            ->field('p.*,d.province_name')
            ->join('data_province d','d.province_id=p.province_id')
            ->where($where)
            ->order("product_id DESC")
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
        $provinces = $this->getProvinces();
        $this->assign('provinces',$provinces);
        return $this->fetch();

    }

    public function edit($product_id = false){
        if ($product_id == false || $product_id != (string)(int)$product_id) {
            $this->error('参数异常！','service/index');
        }
        $data = input('post.');
        if ($data) {
            if(db('u_product') -> where(array('name' =>$data['name']))->where('product_id','neq',$product_id)->find()){
                $this->error('商品名重复!');
            }
            $data['dateline'] = time();
            $data['closed'] = 0;
            $thumb = uploadFile('cover','product'); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
            }
            if (array_key_exists('son_id', $data)) {
                if ($data['son_id']) {
                    $data['cate_id'] = $data['son_id'];
                }
                unset($data['son_id']);
            }
            $res = db('u_product')->where(array('product_id' =>$product_id))->update($data);

            if ($res != false) {
                $this->success('修改成功！');
            }else{
                $this->error('服务器异常！','service/index');
            }
        }else{
            $provinces = $this->getProvinces();
            $this->assign('provinces',$provinces);
            $expert = db('u_product')->where(array('product_id' =>$product_id))->find();
            $this->assign('expert',$expert);
            $cates = $this->getCates(2);
            $this->assign('cates',$cates);
            return $this->fetch();
        }

    }

    public function add(){

        $data = input('post.');
        if ($data) {
            if(db('u_product') -> where(array('name' =>$data['name']))->find()){
                $this->error('商品名重复!');
            }
            $data['dateline'] = time();
            $data['closed'] = 0;
            $thumb = uploadFile('cover','product'); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
            }
            if (array_key_exists('son_id', $data)) {
                if ($data['son_id']) {
                    $data['cate_id'] = $data['son_id'];
                }
                unset($data['son_id']);
            }

            if (db('u_product')->insert($data)) {
                $this->success('添加成功！','service/index');
            }else{
                $this->error('服务器异常！','service/index');
            }
        }else{


            $provinces = $this->getProvinces();
            $this->assign('provinces',$provinces);
            $cates = $this->getCates(2);
            $this->assign('cates',$cates);
            return $this->fetch();
        }
    }

    public function delete($product_id){
        if ($product_id == false || $product_id != (string)(int)$product_id) {
            $this->error('参数异常！','expert/index');
        }

        if (db('u_product')->where(array('product_id' =>$product_id))->update(array('closed'=>1))) {
            $this->success('修改成功！');
        }else{
            $this->error('服务器异常！','expert/index');
        }
    }

    public function getProvinces(){
        return db('data_province')->field('province_id,province_name')->select();
    }





}
