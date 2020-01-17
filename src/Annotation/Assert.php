<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Traits\ParseValue;
use Illuminate\Support\Arr;

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


    function handle($testClass, $request, $responseJson)
    {
        $this->request = $request;
        $this->response = $responseJson;

        array_walk($this->param, [$this, 'walkParam']);
        return call_user_func_array([$testClass, $this->funcName], $this->param);
    }


    /**
     * 把对象处理为字符
     * @param $value
     * @author Blues
     */
    protected function walkParam(&$value)
    {
        if (is_array($value)) {
            array_walk($value, [$this, 'walkParam']);
        } else {
            if (gettype($value) == 'object') {

                if ($value instanceof GetResponse) {
                    $value = $value;
//                    dump($value);
                }
                //获取请求的变量.
                if ($value instanceof GetRequest) {
                    $value->param;
                    //如果没有参数.就是获取全部
                    if (!$value->param) {
                        $value = $this->request['request'];
                    } else {
                        //获取指定的变量名
                        $value = Arr::get($this->request['request'], $value->param);
                    }
                }
            }
        }
    }
}