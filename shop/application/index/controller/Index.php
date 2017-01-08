<?php
namespace app\index\controller;

use app\index\model\Product;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        $product = Db::name("product")->select();
        $this->assign("Product", $product);
        return $this->fetch();
    }

    public function search(){
        $string = $_POST['string'];
        $p = new Product();
        $product = $p->where('pd_name','like','%'.$string.'%')->select();
        $this->assign('Product',$product);
        return $this->fetch('index');
    }
}
