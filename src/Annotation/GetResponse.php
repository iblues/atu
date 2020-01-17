<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Symfony\Component\VarDumper\Dumper\CliDumper;
use Iblues\AnnotationTestUnit\Traits\ParseValue;

/**
 * 检查返回的断言. 需要返回json的. data.id=true,data.title="正则表达式*",
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Response
 * @package Iblues\AnnotationTestUnit
 */
class GetResponse
{
    public $param = null;

    public function __construct($data = [])
    {
        $this->param = $data['value'] ?? null;
    }
}
