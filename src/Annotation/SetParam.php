<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Libs\Param;

/**
 * 标记这次请求的请求参数. 一个testAPi可以有多个TestRequest代表多次请求.
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class SetParam
 * @package Iblues\AnnotationTestUnit
 */
class SetParam
{
    public $param = null;

    public function __construct($data = [])
    {
        $this->param = $data['value'] ?? null;
    }

    public function handel()
    {
//        return Param::param($this->param);
    }

}