<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Traits\ParseValue;

/**
 * 测试的前置函数. 比如要先登录等.
 * @Annotation
 * @author Blues
 * Class Before
 * @package Iblues\AnnotationTestUnit
 */
class Before
{
    use ParseValue;

    protected $funcName;
    protected $param;
    protected $class = null;

    function __construct($data)
    {
        $value = $this->parseConstructValue($data);

        if ($value[0] instanceof Tag) {
            $this->funcName = $value[0];
        } else if (class_exists($value[0])) {
            $this->class = $value[0];
            $this->funcName = $value[1];
            $this->param = $value[2] ?? [];
        } else {
            $this->class = null;
            $this->funcName = $value[0];
            $this->param = $value[1] ?? [];
        }
    }


    function handle($testClass)
    {
        $class = $this->class ?? $testClass;
        if ($this->funcName instanceof Tag) {
            $this->funcName = $this->funcName->getTag()[0];
            $testClass->callATU($this->funcName);
        } else if (method_exists($class, 'callProtectedFunction')) {
            $param = ['func' => $this->funcName, 'param' => $this->param];
            return call_user_func_array([$class, 'callProtectedFunction'], $param);
        } else {
            return call_user_func_array([$class, $this->funcName], $this->param);
        }
    }


}