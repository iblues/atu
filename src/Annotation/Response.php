<?php

namespace Iblues\AnnotationTestUnit\Annotation;

/**
 * 检查返回的断言. 需要返回json的. data.id=true,data.title="正则表达式*",
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Response
 * @Target({"ANNOTATION"})
 * @package Iblues\AnnotationTestUnit
 */
class Response
{

    public function __construct($data = [])
    {
//        dump($data);
    }

}