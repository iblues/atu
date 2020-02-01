<?php

namespace Iblues\AnnotationTestUnit\Annotation;
use App\Models\Common\CommonUser;
use Iblues\AnnotationTestUnit\Libs\Param;
use Iblues\AnnotationTestUnit\Traits\ParseValue;

/**
 * 标记这次请求的请求参数. 一个testAPi可以有多个TestRequest代表多次请求.
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Request
 * @package Iblues\AnnotationTestUnit
 */
class Request
{
    use ParseValue;
    public $request = null;

    public function __construct($data = [])
    {
        $this->request = $data['value'] ?? null;
    }

    public function getJsonRequest()
    {
        return $this->request;
    }

    public function handel($testClass, $method, $url)
    {
        if (is_null($this->request)) {
            return $testClass->json($method, $url);
        } else {
            array_walk($this->request, [$this, 'walkParam']);
            return $testClass->json($method, $url, $this->request);
        }
    }

}