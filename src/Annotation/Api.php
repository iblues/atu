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
    public $urlPath = null;
    protected $httpMethod = null;


    public function __construct($data)
    {
        if (isset($data['path'])) {
            $this->urlPath = $data['path'];
        }
        if (isset($data['method'])) {
            $this->httpMethod = $data['method'];
        }
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
        $method = $this->httpMethod ?? $method;


        if ($this->urlPath) {
            //第一个是/ 就直接用
            if ($this->urlPath[0] == '/') {
                $url = $this->urlPath;
                //如果是http开头也直接用
            } elseif (substr($this->urlPath, 0, 4) == 'http') {
                $url = $this->urlPath;
            } else {
                //如果都不是.那就是单独的path参数. 拼接在后面
                $url = preg_replace('/{.*?}/i', '', $url);
                $url = $url . $this->urlPath;
            }
        }
        $response = $this->request->handel($testClass, $method, $url);
        $this->response->setRespone($response);
        return ['method' => $method, 'url' => $url, 'request' => $this->request->getJsonRequest()];
    }

    public function handleResponse($testClass, $annotation)
    {
        $this->response->assert($annotation);
    }
}