<?php

namespace Iblues\AnnotationTestUnit\Traits;

use Iblues\AnnotationTestUnit\Libs\Annotation;
use Iblues\AnnotationTestUnit\Libs\ApiTestFactory;
use Tests\Feature\AnnotationTest;

trait ApiTest
{


    function doNow()
    {
        $todoList = Annotation::getApiTest(1);
        foreach ($todoList as $todo) {
            $return = new ApiTestFactory($this, $todo);
        }
    }

    function doAll()
    {
        $todoList = Annotation::getApiTest();
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

}


