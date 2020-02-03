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

            $startTime = $this->msectime();
            $request = $annotation->handleRequest($testClass, $this->method, $this->url);
            $response = $annotation->handleResponse($testClass, $annotation, $request);


            //处理跟返回参数无关的assert.比如数据库
            $annotation->handleAssert($testClass, $annotation, $request, $response);

            //如果有@ATU\debug()
            if ($annotation->debug)
                $this->debugInfo($annotation, $startTime, 0);

        } catch (\Exception $e) {
            $this->debugInfo($annotation, $startTime, 1);
            throw $e;
        }
    }

    protected function debugInfo($annotation, $startTime = 0, $error = true)
    {
        $debugInfo = [];
        /**
         * @var $annotation Api
         */
        $debugInfo = $annotation->getResponeDebugInfo();
        if ($error)
            Console::error(' ----------------------------------------- DEBUG -----------------------------------------');
        else
            Console::info(' ----------------------------------------- INFO ------------------------------------------');
        $this->dump('Code', "{$this->methodPath} ( {$this->fileLine} )");
        $this->dump('METHOD', $this->method);
        $this->dump('URL', $request['url'] ?? '');
        if ($startTime)
            $this->dump('Time', (($this->msectime() - $startTime) / 1000) . 's');
        $this->dump('Request', json_encode($request['request'] ?? [], JSON_UNESCAPED_UNICODE));
        foreach ($debugInfo as $key => $info) {
            if ($error && in_array($key, ['ErrorMsg', 'Response'])) {
                $this->dump($key, $info, 1);
            } else {
                $this->dump($key, $info);
            }
        }

        if ($error)
            Console::error(' ----------------------------------------- END -------------------------------------------');
        else
            Console::info(' ----------------------------------------- END -------------------------------------------');

    }

    protected function dump($key, $val, $error = false)
    {
        $strings = explode("\n", $val);
        foreach ($strings as $k => $string) {
            if ($k > 0) {
                $strings[$k] = '                        ' . $string;
            }
        }
        $stirng = implode("\n", $strings);
        $stirng = ' - ' . str_pad($key, 15, ' ', STR_PAD_RIGHT) . ":   {$stirng}";
        if ($error) {
            Console::error($stirng);
        } else {
            Console::dump($stirng);
        }
    }

    /**
     * 返回毫秒
     * @return float
     * @author Blues
     *
     */
    protected function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}
