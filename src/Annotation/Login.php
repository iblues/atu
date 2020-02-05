<?php

namespace Iblues\AnnotationTestUnit\Annotation;

/**
 * 测试的前置函数. 比如要先登录等.
 * @Annotation
 * @author Blues
 * Class Login
 * @package Iblues\AnnotationTestUnit
 */
class Login
{

    public $data;

    function __construct($data = [])
    {
        $this->data = isset($data['value']) ? $data['value'] : true;
    }

    /**
     * @param $testClass
     * @return mixed
     * @author Blues
     *
     */
    public function handel($testClass)
    {
        if ($this->data) {
            return $testClass->login($this->data);
        } else {
            return $testClass->login(null);
        }
    }
}