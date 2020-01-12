<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Annotation\Response;
use Iblues\AnnotationTestUnit\Annotation\Request;

/**
 * 标记这是一个Api测试,一个控制器可以有多个
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class TestApi
 * @package Iblues\AnnotationTestUnit
 * @Target({"METHOD"})
 */
class Api
{
    /**
     * @var \Iblues\AnnotationTestUnit\Annotation\Response;
     */
    public $response;
    /**
     * @var \Iblues\AnnotationTestUnit\Annotation\Request;
     */
    public $request;
    public $debug;
    public $now = 0;


    public function __construct($data)
    {
        foreach ($data['value'] as $param) {
            if ($param instanceof Now) {
                $this->debug = new Debug();
                $this->now = 1;
            } elseif ($param instanceof Request) {
                $this->request = $param;
            } elseif ($param instanceof Response) {
                $this->response = $param;
            } elseif ($param instanceof Debug) {
                $this->debug = $param;
            }

            //如果没有 先赋个默认值
            if (!$this->request) {
                $this->request = new Request();
            }
            if (!$this->response) {
                $this->response = new Response();
            }

        }
//        dump($data);
    }

    /**
     *
     * @param $testClass 测试类
     * @author Blues
     *
     */
    public function handleRequest($testClass, $method = 'GET', $url)
    {
        //处理method,url  put:11.com
        $response = $this->request->handel($testClass, $method, $url);
        $this->response->setRespone($response);
    }

    public function handleResponse($testClass, $annotation)
    {
        $this->response->assert($annotation);
    }
}