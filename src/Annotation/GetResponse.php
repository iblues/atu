<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Illuminate\Support\Arr;
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

    public function handel($jsonRespone)
    {
        //如果没有参数.就是获取全部
        if (!$this->param) {
            $value = $jsonRespone;
        } else {
            //获取指定的变量名
            $value = Arr::get($jsonRespone, $this->param);
        }
        return $value;
    }
}
