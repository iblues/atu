<?php

namespace Iblues\AnnotationTestUnit\Annotation;
use App\Models\Common\CommonUser;

/**
 * 标记这次请求的请求参数. 一个testAPi可以有多个TestRequest代表多次请求.
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Request
 * @Target({"ANNOTATION"})
 * @package Iblues\AnnotationTestUnit
 */
class Request
{
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
        $user = CommonUser::first();
        $testClass = $testClass->actingAs($user, 'api');
        if (is_null($this->request)) {
            return $testClass->json($method, $url);
        } else {
            return $testClass->json($method, $url, $this->request);
        }
    }
}