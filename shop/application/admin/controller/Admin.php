<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2016/12/30
 * Time: 8:19
 */

namespace app\admin\controller;


use app\index\model\Order;
use app\index\model\Product;
use app\user\validate\Client;
use think\Controller;
use think\Db;

class Admin extends Controller
{
    public function login(){
        return $this->fetch('login');
    }

    public function do_login(){

        if(session("adminid") !== null){
            $this->redirect(url("admin/Admin/admin"));
        }else {
            $data["account"] = $_POST["account"];
            $data["password"] = $_POST["password"];

            $a = Db::table('admin')->where("account", $data["account"])->find();
            echo count($a);
            if($a != null && $a['password'] == $data["password"]){
                session("adminid", $a['adminid']);
                $this->success("登录成功!", url('admin/Admin/admin'),'', 1);
            }else{
                echo "账号或密码错误！";
            }
        }

    }

    public function admin(){
        if(session("adminid") === null){
            $this->redirect(url("admin/Admin/login"));
        }else {
            $o = new Order();
            $order_product = '';
            $order = $o->select();
            for($i = 0; $i<count($order); $i++){
                $order_product[$i] = Db::table('order_product')->where('orderid', $order[$i]->orderid)->find();
            }
            echo count($order);
            $this->assign('order', $order);
            $this->assign('order_product', $order_product);

            return $this->fetch('admin');
        }
    }

    public function send_order(){
        $orderid = $_GET['orderid'];
        $o = new Order();
        $o->where('orderid',$orderid)->setField('status','1');
        $this->success('发货成功！', url('admin/Admin/admin'),'', 1);
    }

    public function manage_product(){
        $p = new Product();
        $p = $p->select();
        $this->assign('Product', $p);
        return $this->fetch('manage_product');
    }
    public function add_product(){
        return $this->fetch('add_product');
    }

    public function do_add_pd(){
        $p = new Product();
        $data['pd_name'] = $_POST['pd_name'];
        $data['price'] = $_POST['price'];
        $data['number'] = $_POST['number'];
        $p->data($data)->save();
        $this->success('添加商品成功！', url('admin/Admin/manage_product'),'', 1);
    }

    public function del_pd(){
        $productid = $_GET["productid"];
        $Product = new Product();
        $Product->where('productid',$productid)->delete();
        $this->success('删除商品成功！', url('admin/Admin/manage_product'),'', 1);
    }

    public function manage_client(){
        $c = new \app\user\model\Client();
        $client = $c->select();
        $this->assign('client', $client);
        return $this->fetch('manage_client');
    }
}