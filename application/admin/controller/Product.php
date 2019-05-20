<?php
namespace app\admin\controller;

class Product extends Base
{
    public $pageSize = 25;

    public function index()
    {
        $cates = get_last_cate(1);
        $cate_ids = array_key_value($cates,'cate_id');
        $search = input('post.search');
        $where = ['closed' => 0,'cate_id' => ['in',$cate_ids]];
        if ($search!= false) {
            $list = db("u_product")
            ->where('name|product_id','like','%'.$search.'%')
            ->where($where)
            ->order("product_id DESC")
            ->paginate($this->pageSize,false,array(
                ));
        }else{
            $list = db("u_product")
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
        return $this->fetch();

    }

    public function edit($product_id = false){
        if ($product_id == false || $product_id != (string)(int)$product_id) {
            $this->error('参数异常！','product/index');
        }
        $data = input('post.');
        if ($data) {
            if(db('u_product') -> where(array('name' =>$data['name']))->where('product_id','neq',$product_id)->find()){
                $this->error('商品名重复!');
            }
            $data['dateline'] = time();
            $data['closed'] = 0;
            $thumb = uploadFile('cover','product',428,335); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
                $data['thumb_cover'] = $thumb['thumb_img'];
            }
            $res = db('u_product')->where(array('product_id' =>$product_id))->update($data);

            if ($res != false) {
                $this->success('修改成功！');
            }else{
                $this->error('服务器异常！','product/index');
            }
        }else{
            $cates = $this->getCates(1);
            $this->assign('cates',$cates);
            $product = db('u_product')->where(array('product_id' =>$product_id))->find();
            $this->assign('product',$product);
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
            $thumb = uploadFile('cover','product',428,335); 
            if ($thumb) {
                $data['cover'] = $thumb['img'];
                $data['thumb_cover'] = $thumb['thumb_img'];
            }

            if (db('u_product')->insert($data)) {
                $this->success('添加成功！','product/index');
            }else{
                $this->error('服务器异常！','product/index');
            }
        }else{
            $cates = $this->getCates(1);
            $this->assign('cates',$cates);
            return $this->fetch();
        }
    }

    public function delete($product_id){
        if ($product_id == false || $product_id != (string)(int)$product_id) {
            $this->error('参数异常！','product/index');
        }

        if (db('u_product')->where(array('product_id' =>$product_id))->update(array('closed'=>1))) {
            $this->success('修改成功！');
        }else{
            $this->error('服务器异常！','product/index');
        }
    }





}
