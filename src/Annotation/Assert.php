<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Libs\Param;
use Iblues\AnnotationTestUnit\Traits\ParseValue;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\Types\Object_;

/**
 * 测试的前置函数. 比如要先登录等.
 * @Annotation
 * @author Blues
 * Class Assert
 * @package Iblues\AnnotationTestUnit
 *
 * 在response中的支持预定义断言 参考 https://learnku.com/docs/laravel/6.x/http-tests/5183#assert-cookie
 * 在Api中支持超的函数
 * https://phpunit.readthedocs.io/zh_CN/latest/assertions.html#assertarrayhaskey
 * 以及数据库相关.
 * assertDatabaseHas($table, array $data);    断言数据库表中包含给定的数据。
 * assertDatabaseMissing($table, array $data);    断言数据库中的表不包含给定数据。
 * assertSoftDeleted($table, array $data);    断言给定记录已被软删除。
 * 也可以在test中自行增加自定义函数.
 *
 */
class Assert
{
    use ParseValue;
    protected $funcName = '';
    protected $param = [];
    protected $request;
    protected $response;

    function __construct($data)
    {
        $value = $this->parseConstructValue($data);
        $this->funcName = $value[0];
        $this->param = $value[1] ?? [];
//        call_user_func_array
    }


    function handle($testClass, Array $request, $response)
    {
        $this->request = $request;
        $this->response = $response;

        array_walk($this->param, [$this, 'walkParam']);
        if (method_exists($testClass, 'callProtectedFunction')) {
            $param = ['func' => $this->funcName, 'param' => $this->param];
            return call_user_func_array([$testClass, 'callProtectedFunction'], $param);
        } else {
            return call_user_func_array([$testClass, $this->funcName], $this->param);
        }

    }


}