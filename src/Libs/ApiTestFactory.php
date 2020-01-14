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
        if ($annotation->now) {
            //如果是开启了当前测试. 提醒下哪些开了的 方便随时关闭
            dump('@Test\Now enable in ' . $this->methodPath . '(' . $this->fileLine . ")");
        }
        $request = $annotation->handleRequest($this->testClass, $this->method, $this->url);
        try {
            $response = $annotation->handleResponse($this->testClass, $annotation);
        } catch (\Exception $e) {
            Console::error(' ----------------------------------------- DEBUG -----------------------------------------');
            $num = 10;
            $this->dump('Code', "{$this->methodPath} ( {$this->fileLine} )");
            $this->dump('URL', $request['url']);
            $this->dump('Request', json_encode($request['request'], JSON_UNESCAPED_UNICODE));
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
        $stirng = ' - ' . str_pad($key, 15, ' ', STR_PAD_RIGHT) . ":   $val";
        $strings = explode("\n", $val);
        foreach ($strings as $key => $string) {
            if ($key > 0) {
                $strings[$key] = str_pad($key, 15, ' ', STR_PAD_RIGHT) . $string;
            }
        }
        if ($key == 'ErrorMsg') {
            Console::error($stirng);
        } else {
            Console::dump($stirng);
        }
    }
}
