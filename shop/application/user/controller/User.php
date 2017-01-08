<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2016/12/29
 * Time: 12:42
 */

namespace app\user\controller;


use app\index\model\Order;
use app\index\model\OrderProduct;
use app\index\model\Product;
use app\user\model\Cart;
use app\user\model\Client;
use think\Controller;
use think\Db;
use think\Session;

class User extends Controller  {

    public function go_login(){
        if(session("clientid") !== null){
            $this->redirect(url("index/Index/index"));
        }else {
            return $this->fetch("login");
        }
    }

    public function login(){

        if(isset($_POST["submit"])){

            $data["phone"] = $_POST["phone"];
            $data["password"] = $_POST["password"];

            $result = validate("Client")->scene("login")->check($data);

            if($result === true){
                $Client = model("Client");
                $Client = $Client->where("phone", $data["phone"])->find();
                if($Client !== null && $Client->password === md5($data["password"])){
                    session("clientid", $Client->clientid);
                    $this->success("登录成功！", url("index/Index/index"), '', 1);
                }else{
                    echo "用户名或密码错误！";
                }
            }else{
                echo $result;
            }

        }

    }

    public function logout(){
        session("clientid", null);
        $this->success("注销成功！", url("index/Index/index"), '', 1);
    }



    public function go_register(){

        if(session("clientid") !== null){
            $this->redirect(url("index/Index/index"));
        }else{
            return $this->fetch("register");
        }

    }

    public function register(){
        if(isset($_POST["submit"])){
            $data["phone"] = $_POST["phone"];
            $data["password"] = $_POST["password"];
            $data["cfpwd"] = $_POST["cfpwd"];
            $data["address"] = $_POST["address"];
            $data["username"] = $_POST["name"];

            $result = $this->validate($data,"Client.register");
            if($result === true){
                $Client = new Client();
                $checkphone = $Client->where("phone", $data["phone"])->find();
                $checkname = $Client->where("username", $data["username"])->find();
                if($checkname != null){
                    echo "昵称已存在，请修改。";
                }elseif ($checkphone != null){
                    echo "该手机已经注册！";
                }else{
                    $data["password"] = md5($data["password"]);
                    unset($data["cfpwd"]);
                    $Client->data($data);
                    $Client->save();
                    if($Client->clientid){
                        Session::set('clientid',$Client->clientid);
                        $this->success("注册成功！", url("index/Index/index"), '', 1);
                    }
                }

            }else{
                echo $result;
            }
        }
    }

    public function client_info(){
        if(session("clientid") === null){
            $this->redirect(url("index/Index/index"));
        }else{
            $Client = model("Client");
            $Client = $Client->where("clientid", session("clientid"))->find();
            $data["phone"] = $Client->phone;
            $data["username"] = $Client->username;
            $data["address"] = $Client->address;
            $data["reg_time"] = $Client->reg_time;
            $data["money"] = $Client->money;
            $this->assign("data", $data);
            return $this->fetch("client_info");
        }
    }

    public function modify_client_info(){

        if(isset($_POST["submit"])){

            $data["password"] = $_POST["password"];
            $data["cfpwd"] = $_POST["cfpwd"];
            $data["address"] = $_POST["address"];
            $data["username"] = $_POST["name"];

            $result = $this->validate($data,"Client.modify");
            if($result === true){

                $Client = model("Client");
                $checkname = $Client->where("username", $data["username"])->find();
                if($checkname != null){
                    echo "昵称已存在，请修改。";
                }else{

                    $data["password"] = md5($data["password"]);
                    unset($data["cfpwd"]);
                    try{
                        $Client->save($data,['clientid' => session("clientid")]);
                    }catch (\mysqli_sql_exception $e){
                        die("数据出错");
                    }
                    $this->success("保存成功！", url(user/User/client_info), '', 1);

                }


            }else{
                echo $result;
            }

        }

    }

    public function add_product_to_cart(){

        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $data["productid"] = $_GET["productid"];
            $data["clientid"] = session("clientid");
            $c = model("Cart");
            $p = $c->where($data)->find();
            if($p !== null){
                try{
                    $c->save([
                        'number' => $p->number+1
                    ], $data);
                }catch (\mysqli_sql_exception $exception){
                    echo "数据出错！";
                }
            }else{
                $data["number"] = 1;
                try{
                    $c->data($data)->save();
                }catch (\mysqli_sql_exception $exception){
                    echo "数据出错！";
                }
            }
            $this->success("添加成功！", url("index/Index/index"), '', 1);
        }

    }

    public function cart(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $cart = new Cart();
            $product_in_cart = $cart->where('clientid', session('clientid'))->select();
            $p = new Product();
            $data  = '';
            $total = 0;
            for($i = 0; $i<count($product_in_cart); $i++){
                $data[$i]["productid"] = $product_in_cart[$i]->productid;
                $pr = $p->where('productid', $product_in_cart[$i]->productid)->find();
                $data[$i]["pd_name"] = $pr->pd_name;
                $data[$i]["price"] = $pr->price;
                $data[$i]["number"] = $product_in_cart[$i]->number;
                $total += $pr->price * $product_in_cart[$i]->number;
            }

            $this->assign('data', $data);
            $this->assign('total', $total);
            return $this->fetch("cart");
        }
    }

    public function go_add_money(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $c = new Client();
            $money = $c->where('clientid', session("clientid"))->value('money');
            $this->assign('money', $money);
            return $this->fetch('add_money');
        }
    }

    public function add_money(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $add_money = $_POST['add_money'];
            $data['add_money'] = $add_money;
            $result = $this->validate($data,"Client.add_money");
            if($result === true){
                $c = new Client();
                $c->where('clientid', session("clientid"))->setInc('money', $add_money);
                $this->success("充值成功！", url("user/User/go_add_money"), '', 1);
            }else{
                echo $result;
            }

        }
    }

    public function delect_product_in_cart(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $productid = $_GET["productid"];
            $cart = new Cart();
            $cart->where('clientid',session("clientid"))->where('productid', $productid)->delete();
            $this->success("删除成功！", url("user/User/cart"), '', 1);
        }
    }

    public function go_check_out(){
        if(session("clientid") === null){
            $this->redirect(url("index/Index/index"));
        }else{
            $cart = new Cart();
            $product_in_cart = $cart->where('clientid', session('clientid'))->select();
            $p = new Product();
            $data  = '';
            $total = 0;
            for($i = 0; $i<count($product_in_cart); $i++){
                $data[$i]["productid"] = $product_in_cart[$i]->productid;
                $pr = $p->where('productid', $product_in_cart[$i]->productid)->find();
                $data[$i]["pd_name"] = $pr->pd_name;
                $data[$i]["price"] = $pr->price;
                $data[$i]["number"] = $product_in_cart[$i]->number;
                $total += $pr->price * $product_in_cart[$i]->number;
            }

            $client = new Client();
            $phone = $client->where('clientid', session('clientid'))->value('phone');
            $address = $client->where('clientid', session('clientid'))->value('address');

            $this->assign('data',$data);
            $this->assign('total',$total);
            $this->assign('phone',$phone);
            $this->assign('address',$address);
            return $this->fetch('checkout');
        }
    }

    public function checkout(){
        if(session("clientid") === null){
            $this->redirect(url("index/Index/index"));
        }else{
            $cart = new Cart();
            $product_in_cart = $cart->where('clientid', session('clientid'))->select();
            $p = new Product();
            $data  = '';
            $total = 0;
            for($i = 0; $i<count($product_in_cart); $i++){
                $data[$i]["productid"] = $product_in_cart[$i]->productid;
                $pr = $p->where('productid', $product_in_cart[$i]->productid)->find();
                $data[$i]["pd_name"] = $pr->pd_name;
                $data[$i]["price"] = $pr->price;
                $data[$i]["number"] = $product_in_cart[$i]->number;
                $total += $pr->price * $product_in_cart[$i]->number;
            }

            $client = new Client();
            $money = $client->where('clientid', session('clientid'))->value('money');
            if($money < $total){
                $this->error('余额不足！请充值！', url("user/User/go_add_money"), '' ,1);
            }else{
                $order = new Order();
                $orderdata['order_phone'] = $_POST['phone'];
                $orderdata['order_address'] = $_POST['address'];
                $orderdata['clientid'] = session("clientid");
                $orderdata['total_price'] = $total;
                $order->data($orderdata)->save();

                $order_product = new OrderProduct();
                for($i = 0; $i<count($data); $i++){
                    $o_product['orderid'] = $order->orderid;
                    $o_product['productid'] = $data[$i]['productid'];
                    $o_product['old_price'] = $data[$i]['price'];
                    $o_product['number'] = $data[$i]['number'];
                    $order_product->data($o_product)->isUpdate(false)->save();
                    $p->where('productid', $data[$i]['productid'])->setDec('number',$data[$i]['number']);
                    //echo $order->orderid."||".$data[$i]['productid']."||".$data[$i]['price']."||".$data[$i]['number']."<br>";
                }
                $client->where('clientid', session('clientid'))->setDec('money',$total);
                $cart->where('clientid', session('clientid'))->delete();
                $this->success("购买成功！", url('index/Index/index'), '', 1);

            }
        }

    }

    public function order_info(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $o = new Order();
            $order_product = '';
            $order = $o->where('clientid', session('clientid'))->select();
            for($i = 0; $i<count($order); $i++){
                $order_product[$i] = Db::table('order_product')->where('orderid', $order[$i]->orderid)->select();
            }
            $this->assign('order', $order);
            $this->assign('order_product', $order_product);
            return $this->fetch('order_info');
        }
    }

    public function delete_order(){
        if(session("clientid") === null){
            $this->redirect("go_login");
        }else{
            $orderid = $_GET['orderid'];
            Db::table('order')->where('orderid',$orderid)->delete();
            $this->success("取消订单成功!", url('user/User/order_info'), '' ,1);
        }
    }



}
