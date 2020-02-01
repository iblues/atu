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