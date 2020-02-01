<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use App\Models\Common\CommonUser;
use Illuminate\Support\Arr;

/**
 * 标记这次请求的请求参数. 一个testAPi可以有多个TestRequest代表多次请求.
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author GetRequest
 * Class Request
 * @package Iblues\AnnotationTestUnit
 */
class GetRequest
{
    public $param = null;

    public function __construct($data = [])
    {
        $this->param = $data['value'] ?? null;
    }

    public function handel($request)
    {
        //如果没有参数.就是获取全部
        if (!$this->param) {
            $value = $request;
        } else {
            //获取指定的变量名
            $value = Arr::get($request, $this->param);
        }
        return $value;
    }

}