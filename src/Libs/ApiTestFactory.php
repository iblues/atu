<?php


namespace Iblues\AnnotationTestUnit\Libs;


use Iblues\AnnotationTestUnit\Annotation\Api;
use Iblues\AnnotationTestUnit\Annotation\TestApi;

class ApiTestFactory
{

    public $debug = 1;

    public $url = '';
    public $method = 'GET';
    public $fileLine = null;
    public $methodPath = null;
    public $testClass = null;
    protected $request;

    public function __construct($testClass, $param)
    {
        $this->testClass = $testClass;
        $this->url = $param['url'];
        $this->method = $param['httpMethod'];
        $this->fileLine = $methodLine = 'file://' . $param['classPath'] . ':' . $param['methodStartLine'];
//        $response = $this->json('GET', '/api/admin/admin/nav');
//        $response
//            ->assertStatus(200)
//            ->assertJson([
//                'code' => true,
//            ]);
        $this->methodPath = $param['path'];
        foreach ($param['annotation'] as $annotation) {
            $this->walkAnnotation($annotation);
        }


    }

    public function walkAnnotation(Api $annotation)
    {

        $testClass = clone($this->testClass);

        if ($annotation->now) {
            //如果是开启了当前测试. 提醒下哪些开了的 方便随时关闭
            dump('@ATU\Now enable in ' . $this->methodPath . '(' . $this->fileLine . ")");

        }
        try {
            $annotation->handleBofore($testClass);
            $request = $annotation->handleRequest($testClass, $this->method, $this->url);
            $response = $annotation->handleResponse($testClass, $annotation, $request);
            //处理跟返回参数无关的assert.比如数据库
            $annotation->handleAssert($testClass, $annotation, $request, $response);
        } catch (\Exception $e) {
            Console::error(' ----------------------------------------- DEBUG -----------------------------------------');
            $this->dump('Code', "{$this->methodPath} ( {$this->fileLine} )");
            $this->dump('URL', $request['url'] ?? '');
            $this->dump('Request', json_encode($request['request'] ?? null, JSON_UNESCAPED_UNICODE));
            if (property_exists($e, 'debugInfo')) {
                foreach ($e->debugInfo as $key => $info) {
                    $this->dump($key, $info);
                }
            }
            throw $e;
        }
    }

    protected function dump($key, $val)
    {
        $strings = explode("\n", $val);
        foreach ($strings as $k => $string) {
            if ($k > 0) {
                $strings[$k] = '                        ' . $string;
            }
        }
        $stirng = implode("\n", $strings);
        $stirng = ' - ' . str_pad($key, 15, ' ', STR_PAD_RIGHT) . ":   {$stirng}";
        if (in_array($key, ['ErrorMsg', 'Response'])) {
            Console::error($stirng);
        } else {
            Console::dump($stirng);
        }
    }
}
