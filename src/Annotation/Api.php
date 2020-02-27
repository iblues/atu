<?php

namespace Iblues\AnnotationTestUnit\Annotation;

use Iblues\AnnotationTestUnit\Annotation\Request;
use Iblues\AnnotationTestUnit\Annotation\Response;
use Iblues\AnnotationTestUnit\Annotation\GetParam;
use Iblues\AnnotationTestUnit\Traits\ParseValue;

/**
 * 标记这是一个Api测试,一个控制器可以有多个
 * @link https://github.com/iblues/annotation-test-unit
 * @Annotation
 * @author Blues
 * Class Api
 * @package Iblues\AnnotationTestUnit
 * @Target({"METHOD"})
 */
class Api
{
    use ParseValue;
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
    public $path = null;
    public $author = '';
    protected $tag;
    public $title = "";
    protected $method = null;
    protected $assert = [];
    protected $before = [];
    protected $fullRequest = [];
    /**
     * @var Login
     */
    public $login;


    public function __construct($data)
    {


        if (isset($data['path'])) {
            $this->path = $data['path'];
        }
        if (isset($data['method'])) {
            $this->method = $data['method'];
        }


        $data['value'] = $data['value'] ?? [];
        //如果value只有一个参数 转成数组
        $data['value'] = is_array($data['value']) ? $data['value'] : [$data['value']];
        $this->title = $data['title'] ?? '';
        foreach ($data['value'] as $param) {

            if ($param instanceof Now) {
                $this->now = 1;
            } elseif ($param instanceof Request) {
                $this->request = $param;
            } elseif ($param instanceof Response) {
                $this->response = $param;
            } elseif ($param instanceof Debug) {
                $this->debug = $param;
            } elseif ($param instanceof Login) {
                $this->login = $param;
            } elseif ($param instanceof Assert) {
                $this->assert[] = $param;
            } elseif ($param instanceof Before) {
                $this->before[] = $param;
            } elseif ($param instanceof Tag) {
                $this->tag[] = $param;
            }
        }

        //如果没有 先赋个默认值
        if (!$this->request) {
            $this->request = new Request();
        }
        if (!$this->response) {
            $this->response = new Response();
        }
        if (!$this->login) {
            $this->login = new Login();
        }
//        dump($data);
    }

    /**
     * 开始前的前置函数, 比如生成用户, 也可以登录,修改url等操作.
     * @param $testClass
     * @param $method
     * @param $url
     * @author Blues
     *
     */
    public function handleBofore($testClass)
    {
//        dump($this->urlPath);
        foreach ($this->before as $before) {
            /**
             * @var $before Before
             */
            try {
                $before->handle($testClass);
            } catch (\Exception $e) {
                dump('回调错误' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
                throw $e;
            }
        }

    }

    protected function handelUrl($urlPath, $originUrl)
    {
        if (!is_null($urlPath) && $urlPath !== '') {

            //如果不是数组的话. 先改成数组 统一处理
            if (!is_array($urlPath)) {
                $urlPath = [$urlPath];
            }

            //检查里面有没有getParam
            foreach ($urlPath as $key => $path) {
                if ($path instanceof GetParam) {
                    $urlPath[$key] = $path->handel();
                }
            }

            //第一个是/ 是根目录
            if ($urlPath[0][0] == '/') {
                $url = implode('', $urlPath);
                //如果是http开头也直接用
            } elseif (substr($urlPath[0], 0, 4) == 'http') {
                $url = implode('', $urlPath);
            } else {
                //如果都不是.那就是单独的path参数. 拼接在后面
                $url = preg_replace_array('/{.*?}/i', $urlPath, $originUrl);
            }

            $originUrl = $url;
        }

        return $originUrl;
    }

    /**
     * @param $testClass
     * @param string $method
     * @param $url
     * @return array
     * @author Blues
     *
     */
    public function handleRequest($testClass, $method = 'GET', $url)
    {
        //处理method,url  put:11.com
        $method = $this->method ?? $method;

        $url = $this->handelUrl($this->path, $url);
//        $this->urlPath= $url;

        $this->login->handel($testClass);

        $response = $this->request->handel($testClass, $method, $url);
        $this->response->setRespone($response);
        $fullRequest = ['method' => $method, 'url' => $url, 'request' => $this->request->getJsonRequest()];
        $this->fullRequest = $fullRequest;
        return $fullRequest;
    }

    public function handleResponse($testClass, $annotation, $request)
    {
        $this->response->setRequest($request);
        $this->response->assert($annotation, $request);
        return $this->response;
    }

    public function getResponeDebugInfo()
    {
        return $this->response->debugInfo;
    }

    public function handleAssert($testClass, $annotation, $request, $response)
    {
        foreach ($this->assert as $assert) {
            /**
             * @var $assert Assert
             */
            $assert->handle($testClass, $request, $response);
        }
    }

    /**
     * 检查是不是在tag中
     * @param null $target
     * @return bool
     * @author Blues
     */
    public function inTag($target = null)
    {
        //如果没有目标tag
        if (is_null($target)) {
            return true;
        }

        $matchTag = $this->tag;
        //有设置tag
        if ($matchTag) {
            $matchTag = $this->tag[0];
            $return = array_intersect($target, $matchTag->getTag());
            return count($return) > 0 ? true : false;
        } else {
            return false;
        }
    }

    /**
     * 是否声明了now
     * @return int
     * @author Blues
     *
     */
    public function isNow()
    {
        return $this->now;
    }
}