<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Tests\Feature\AnnotationTest;

trait ApiTest
{


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
     * @param int $id
     * @return AnnotationTest
     * @author Blues
     */
    public function login($id = true)
    {
        if ($id == false) {
            return $this;
        }
        $class = class_exists(\App\Models\Common\CommonUser::class) ? \App\Models\Common\CommonUser::class : \App\User::class;
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

}


