<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\Param;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Tests\Feature\AnnotationTest;

trait ApiTest
{
    /**
     * 用来存储param相关变量
     * @var array
     */
    public $param=[];

    function doNow()
    {
        $cache = $this->cache ?? true;
        $todoList = Annotation::getApiTest(['now' => 1], $cache);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }

    function doAll()
    {
        $cache = $this->cache ?? true;
        $todoList = Annotation::getApiTest([], $cache);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }


    /**
     * 模拟登录. 如果没有会出错!
     * must have it!
     * @param bool $id
     * @return AnnotationTest
     * @throws \Exception
     * @author Blues
     */
    public function login($id = true)
    {
        if ($id == false) {
            return $this;
        }
        if (property_exists($this, 'userModel')) {
            $class = $class = $this->userModel;
        }
        else {
            $class = class_exists(\App\Models\Common\CommonUser::class) ? \App\Models\Common\CommonUser::class : \App\User::class;
        }

        if(!class_exists($class)){
            throw new \Exception("$class not exist, Please use \$this->userModel override it");
        }
        $user = $id ? $class::find($id) : $class::first();
        return $this->actingAs($user, $this->guard ?? 'api');
    }

    /**
     * 避免变量污染,尽可能让每一次测试都独立. 目前发现的是登录会影响
     * @author Blues
     */
    public function __clone()
    {
        $this->app = $this->createApplication();
    }

    /**
     * 设置变量 通过getParam()调用
     * @param $key
     * @param $data
     * @return boolean|array
     * @author Blues
     *
     */
    public function setParam($key,$data){
        return Param::Param($key,$data);
    }

    /**
     * 设置变量 通过setParam()设置
     * @param $key
     * @return boolean|array
     * @author Blues
     *
     */
    public function getParam($key){
        return Param::Param($key);
    }

    /**
     * 由于有些断言是私有方法. 开个接口来调
     * @param $func
     * @param $param
     * @return mixed
     * @author Blues
     *
     */
    public function callProtectedFunction($func, $param)
    {
        return call_user_func_array([$this, $func], $param);
    }



    protected function getRandPhone(){
        return '1'.rand(1300000000,9999999999);
    }

    protected function getRandEmail(){
        return str_random(10).'@gmail.com';
    }

    protected function getPassword(){
        return bcrypt('secret');
    }

}


