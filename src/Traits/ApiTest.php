<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\Param;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Iblues\AnnotationTestUnit\Libs\Routes;
use Tests\Feature\AnnotationTest;

trait ApiTest
{
    /**
     * 用来存储param相关变量
     * @var array
     */
    public $param = [];
    public $loginUser = null;

    /**
     * 测试带有@ATU\Api和@ATU\Now注解的
     * @author Blues
     *
     */
    protected function doNow($tag = null)
    {
        $cache = $this->cache ?? true;
        $todoList = Annotation::getApiTest(['now' => 1, 'whiteList' => $this->whiteList ?? [], 'blackList' => $this->blackList ?? []], $cache);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo, ['tag' => $tag, 'now' => 1, 'debug' => $this->debug ?? false]);
        }
    }

    /**
     * 测试带有@ATU\Api()注解的
     * @author Blues
     */
    protected function doAll($tag = null)
    {
        $cache = $this->cache ?? true;
        $todoList = Annotation::getApiTest(['whiteList' => $this->whiteList ?? [], 'blackList' => $this->blackList ?? []], $cache);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo, ['tag' => $tag, 'debug' => $this->debug ?? false]);
        }
    }


    /**
     * 读取所有带有@ATU\Api主键的,看是否有对应的路由匹配. 如果没有匹配路由就报错. 可用@ignore暂时忽略
     * @author Blues
     */
    protected function checkRouter()
    {
        Routes::checkAllRoute($this);
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
        $driver = $this->guard ?? 'api';
        if ($id == false) {
            $id = $this->app['auth']->guard($driver)->id();
            if ($id) {
                $this->app['auth']->guard($driver)->logout();
                $this->app['auth']->shouldUse($driver);
            }
            return $this;
        }
        if (property_exists($this, 'userModel')) {
            $class = $class = $this->userModel;
        } else {
            $class = class_exists(\App\Models\Common\CommonUser::class) ? \App\Models\Common\CommonUser::class : \App\User::class;
        }

        if (!class_exists($class)) {
            throw new \Exception("$class not exist, Please use \$this->userModel override it");
        }
        $user = $id ? $class::find($id) : $class::first();
        $this->loginUser = $user;
        return $this->actingAs($user, $driver);
    }

    /**
     * 避免变量污染,尽可能让每一次测试都独立. 目前发现的是登录会影响
     * @author Blues
     */
    public function __clone()
    {
        $isolateApp = $this->isolateApp ?? true;
        if ($isolateApp) {
            $this->refreshApplication();
//            $this->callBeforeApplicationDestroyedCallbacks();
//            $this->app = $this->createApplication();
//            $this->setUp();
        }
    }

    /**
     * 设置变量 通过getParam()调用
     * @param $key
     * @param $data
     * @return boolean|array
     * @author Blues
     *
     */
    public function setParam($key, $data)
    {
        return Param::Param($key, $data);
    }

    /**
     * 设置变量 通过setParam()设置
     * @param $key
     * @return boolean|array
     * @author Blues
     *
     */
    public function getParam($key)
    {
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


    protected function getRandPhone()
    {
        return '1' . rand(1300000000, 9999999999);
    }

    protected function getRandEmail()
    {
        return str_random(10) . '@gmail.com';
    }

    protected function getPassword()
    {
        return bcrypt('secret');
    }

}


