<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/29
 * Time: 20:23
 */

namespace app\user\validate;


use think\Validate;

class Client extends Validate
{
    protected $rule = [
        'username'  =>  'require|max:10|alphaDash',
        'phone' => 'require|length:11|number|phone_number',
        'password' => 'require|length:8,16',
        'cfpwd' => 'require|confirm:password',
        'address' =>  'require',
        'money' => 'require|number'
    ];

    protected $message = [
        'username.require'  =>  '昵称不能为空！',
        'username.max' => '昵称最大长度为10！',
        'username.alphaDash' => '昵称只能含有字母和数字，\'_\'及\'-\'',
        'phone.require' => '请输入手机号码！',
        'phone.length' => '请输入正确格式的手机号码！',
        'phone.number' => '请输入正确格式的手机号码！',
        'phone.phone_number' => '请输入正确格式的手机号码！',
        'password.require' => '密码不能为空！',
        'password.length' => '密码长度应为8~16位！',
        'cfpwd.require' => '请再次输入密码！',
        'cfpwd.confirm' => '两次输入的密码不一致！',
        'address.require' => '请输入地址！',
        'add_money.require'=> '请输入正确金额！',
        'add_money.number' => '请输入正确金额！',

    ];

    protected $scene = [

        'register' => ['username','phone','password','cfpwd','address'],
        'login' => ['phone', 'password'],
        'modify' => ['username', 'password', 'cfpwd', 'address'],
        'add_money' => ['add_money'],

    ];

    protected function phone_number($value,$rule,$data){
        return preg_match("/^(0|86|17951)?(13[0-9]|15[012356789]|17[0135678]|18[0-9]|14[579])[0-9]{8}$/", $value)
            ? true : false ;
    }
    
}