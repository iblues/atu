<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 标记该测试未需要测试
 * @author Blues
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * Class Now
 * @package Iblues\AnnotationTestUnit
 */
class Now
{
    public function __construct()
    {
    }
}