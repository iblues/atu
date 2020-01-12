<?php

namespace Iblues\AnnotationTestUnit\Annotation;


/**
 * 标记是否开启debug. 如果开启. 会输出返回的详情内容
 * @author Blues
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * Class Debug
 * @Target({"ANNOTATION"})
 * @package Iblues\AnnotationTestUnit
 */
class Debug
{
    public function __construct($data = [])
    {
    }
}